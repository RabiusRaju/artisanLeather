<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    // GET /api/v1/surveys/:slug
    public function show(Request $request, string $slug)
    {
        $survey = Survey::with('questions')->where('slug', $slug)->firstOrFail();

        if (!$survey->isActive()) {
            return response()->json(['error' => 'Survey is not currently active.'], 403);
        }

        // Check for duplicate submission (token + IP dual check)
        if (!$survey->allow_multiple_responses) {
            $token = $request->header('X-Survey-Token') ?? $request->query('token');
            $tokenCompleted = $token && SurveyResponse::where('survey_id', $survey->id)
                ->where('session_token', $token)->whereNotNull('completed_at')->exists();
            $ipCompleted = SurveyResponse::where('survey_id', $survey->id)
                ->where('ip_address', $request->ip())->whereNotNull('completed_at')
                ->where('created_at', '>=', now()->subDays(7))->exists();

            if ($tokenCompleted || $ipCompleted) {
                return response()->json(['error' => 'You have already completed this survey.'], 409);
            }
        }

        return response()->json([
            'data' => [
                'id'               => $survey->id,
                'title'            => $survey->title,
                'title_ar'         => $survey->title_ar,
                'slug'             => $survey->slug,
                'description'      => $survey->description,
                'description_ar'   => $survey->description_ar,
                'show_progress'    => $survey->show_progress,
                'is_anonymous'     => $survey->is_anonymous,
                'thank_you_message'=> $survey->thank_you_message ?? 'Thank you for your feedback!',
                'thank_you_message_ar' => $survey->thank_you_message_ar ?? 'شكراً لك على ملاحظاتك!',
                'redirect_url'     => $survey->redirect_url,
                'questions'        => $survey->questions->map(fn($q) => [
                    'id'          => $q->id,
                    'type'        => $q->type,
                    'question'    => $q->question,
                    'question_ar' => $q->question_ar,
                    'description' => $q->description,
                    'options'     => $q->options,
                    'options_ar'  => $q->options_ar,
                    'is_required' => $q->is_required,
                    'settings'    => $q->settings ?? [],
                    'sort_order'  => $q->sort_order,
                ]),
            ],
        ]);
    }

    // POST /api/v1/surveys/:slug/respond
    public function respond(Request $request, string $slug)
    {
        $survey = Survey::with('questions')->where('slug', $slug)->firstOrFail();

        if (!$survey->isActive()) {
            return response()->json(['error' => 'Survey is not currently active.'], 403);
        }

        // C-5 FIX: IP-based rate limiting — max 5 submissions per IP per survey per hour
        $rateLimitKey = 'survey:' . $survey->id . ':' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'error' => 'Too many responses. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, decaySeconds: 3600);

        // C-5 FIX: Server-side token — always generate server-side, never trust client token alone.
        // Accept client token for dedup check, but generate a new one if absent.
        $clientToken = $request->header('X-Survey-Token');
        $token = $clientToken ?: Str::random(40);

        // C-5 FIX: Dual deduplication — by token AND by IP (covers token-omission bypass)
        if (!$survey->allow_multiple_responses) {
            // Check token-based dedup
            $tokenExists = $clientToken && SurveyResponse::where('survey_id', $survey->id)
                ->where('session_token', $clientToken)
                ->whereNotNull('completed_at')
                ->exists();

            // Check IP-based dedup (catches token omission attack)
            $ipExists = SurveyResponse::where('survey_id', $survey->id)
                ->where('ip_address', $request->ip())
                ->whereNotNull('completed_at')
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if ($tokenExists || $ipExists) {
                return response()->json(['error' => 'You have already completed this survey.'], 409);
            }
        }

        $validated = $request->validate([
            'answers'             => 'required|array',
            'answers.*'           => 'nullable',
            'respondent_name'     => 'nullable|string|max:100',
            'respondent_email'    => 'nullable|email|max:255',
            'respondent_country'  => 'nullable|string|max:100',
        ]);

        // Validate required questions are answered
        $requiredIds = $survey->questions->where('is_required', true)->pluck('id')->toArray();
        $answeredIds = array_keys(array_filter($validated['answers'], fn($a) => !empty($a)));
        $missing = array_diff($requiredIds, $answeredIds);
        if (!empty($missing)) {
            return response()->json([
                'error'   => 'Please answer all required questions.',
                'missing' => array_values($missing),
            ], 422);
        }

        // Create response record
        $response = SurveyResponse::create([
            'survey_id'          => $survey->id,
            'session_token'      => $token,
            'ip_address'         => $request->ip(),
            'respondent_name'    => $validated['respondent_name'] ?? null,
            'respondent_email'   => $validated['respondent_email'] ?? null,
            'respondent_country' => $validated['respondent_country'] ?? null,
            'completed_at'       => now(),
        ]);

        // M-4 FIX: Only save answers for questions that belong to THIS survey
        $validQuestionIds = $survey->questions->pluck('id')->toArray();
        foreach ($validated['answers'] as $questionId => $answerValue) {
            if ($answerValue === null || $answerValue === '') continue;
            if (!in_array((int)$questionId, $validQuestionIds)) continue; // reject foreign question IDs
            SurveyAnswer::create([
                'survey_response_id' => $response->id,
                'survey_question_id' => (int)$questionId,
                'answer'             => is_array($answerValue) ? $answerValue : [$answerValue],
            ]);
        }

        return response()->json([
            'success' => true,
            'token'   => $token,
            'message' => $survey->thank_you_message ?? 'Thank you for your feedback!',
        ], 201);
    }
}

<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
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

        // Check for duplicate submission (session-based)
        if (!$survey->allow_multiple_responses) {
            $token = $request->header('X-Survey-Token') ?? $request->query('token');
            if ($token && SurveyResponse::where('survey_id', $survey->id)
                                        ->where('session_token', $token)
                                        ->whereNotNull('completed_at')
                                        ->exists()) {
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

        // Generate or reuse session token
        $token = $request->header('X-Survey-Token') ?? Str::random(32);

        // Duplicate submission check
        if (!$survey->allow_multiple_responses) {
            if (SurveyResponse::where('survey_id', $survey->id)
                              ->where('session_token', $token)
                              ->whereNotNull('completed_at')
                              ->exists()) {
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

        // Save each answer
        foreach ($validated['answers'] as $questionId => $answerValue) {
            if ($answerValue === null || $answerValue === '') continue;
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

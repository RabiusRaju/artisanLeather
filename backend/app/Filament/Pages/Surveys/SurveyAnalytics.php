<?php
namespace App\Filament\Pages\Surveys;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Enums\NavigationGroupEnum;
use Filament\Pages\Page;

class SurveyAnalytics extends Page
{
    protected string $view = 'filament.pages.surveys.survey-analytics';
    public static function getNavigationIcon(): string  { return 'heroicon-o-chart-pie'; }
    public static function getNavigationGroup(): string { return NavigationGroupEnum::Analytics->value; }
    public static function getNavigationSort(): int     { return 6; }
    public static function getNavigationLabel(): string { return 'Survey Analytics'; }
    public function getTitle(): string                  { return 'Survey Analytics Dashboard'; }

    public function getData(): array
    {
        $now = now();

        // ── KPI Summary ───────────────────────────────────────────────────
        $totalSurveys    = Survey::count();
        $activeSurveys   = Survey::where('status', 'active')->count();
        $totalResponses  = SurveyResponse::whereNotNull('completed_at')->count();
        $thisWeek        = SurveyResponse::whereNotNull('completed_at')
                            ->where('created_at', '>=', $now->copy()->startOfWeek())->count();
        $thisMonth       = SurveyResponse::whereNotNull('completed_at')
                            ->where('created_at', '>=', $now->copy()->startOfMonth())->count();
        $totalStarted    = SurveyResponse::count();
        $completionRate  = $totalStarted > 0
                            ? round(($totalResponses / $totalStarted) * 100, 1) : 0;

        // ── Daily responses — last 30 days ────────────────────────────────
        $dailyLabels = $dailyCounts = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i);
            $dailyLabels[] = $d->format('d M');
            $dailyCounts[] = SurveyResponse::whereNotNull('completed_at')
                                ->whereDate('created_at', $d->toDateString())->count();
        }

        // ── Per-survey stats ──────────────────────────────────────────────
        $surveys = Survey::withCount(['responses', 'questions'])
            ->get()
            ->map(function ($s) {
                $completed = $s->responses()->whereNotNull('completed_at')->count();
                $total     = $s->responses_count;
                $latest    = $s->responses()->whereNotNull('completed_at')
                                ->latest('completed_at')->first()?->completed_at;
                return [
                    'id'              => $s->id,
                    'title'           => $s->title,
                    'slug'            => $s->slug,
                    'status'          => $s->status,
                    'questions_count' => $s->questions_count,
                    'total'           => $total,
                    'completed'       => $completed,
                    'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
                    'latest'          => $latest?->diffForHumans() ?? 'No responses yet',
                    'url'             => "https://artisanleatherom.com/survey/{$s->slug}",
                ];
            });

        // ── Survey responses bar (per survey) ─────────────────────────────
        $surveyLabels = $surveys->map(fn($s) =>
            mb_strlen($s['title']) > 30 ? mb_substr($s['title'], 0, 30) . '…' : $s['title']
        )->toArray();
        $surveyCounts = $surveys->pluck('completed')->toArray();

        // ── NPS Score ─────────────────────────────────────────────────────
        $npsQuestion = SurveyQuestion::where('type', 'nps')->first();
        $npsScore    = null;
        $npsBreakdown = ['promoters' => 0, 'passives' => 0, 'detractors' => 0, 'total' => 0];
        if ($npsQuestion) {
            $scores = SurveyAnswer::where('survey_question_id', $npsQuestion->id)->get()
                ->map(fn($a) => (int)(is_array($a->answer) ? ($a->answer[0] ?? 0) : $a->answer));
            $p = $scores->filter(fn($s) => $s >= 9)->count();
            $pa = $scores->filter(fn($s) => $s >= 7 && $s <= 8)->count();
            $d = $scores->filter(fn($s) => $s <= 6)->count();
            $t = $scores->count();
            $npsScore = $t > 0 ? round((($p - $d) / $t) * 100) : null;
            $npsBreakdown = ['promoters' => $p, 'passives' => $pa, 'detractors' => $d, 'total' => $t];
        }

        // ── Top answers across product preference ─────────────────────────
        $colourQ = SurveyQuestion::where('type', 'multiple_choice')
            ->where('question', 'like', '%colour%')
            ->orWhere('question', 'like', '%color%')
            ->first();
        $topColours = [];
        if ($colourQ) {
            $tally = [];
            SurveyAnswer::where('survey_question_id', $colourQ->id)->get()
                ->each(function ($a) use (&$tally) {
                    foreach ((array)$a->answer as $v) {
                        $tally[$v] = ($tally[$v] ?? 0) + 1;
                    }
                });
            arsort($tally);
            $topColours = array_slice($tally, 0, 5, true);
        }

        // ── Avg rating across all rating questions ────────────────────────
        $ratingAnswers = SurveyAnswer::whereHas('question', fn($q) => $q->where('type', 'rating'))->get();
        $avgRating = $ratingAnswers->count() > 0
            ? round($ratingAnswers->avg(fn($a) => (float)(is_array($a->answer) ? ($a->answer[0] ?? 0) : $a->answer)), 1)
            : null;

        // ── Recent responses ──────────────────────────────────────────────
        $recentResponses = SurveyResponse::with('survey')
            ->whereNotNull('completed_at')
            ->latest('completed_at')
            ->limit(8)
            ->get()
            ->map(fn($r) => [
                'survey'    => $r->survey?->title,
                'country'   => $r->respondent_country,
                'completed' => $r->completed_at->diffForHumans(),
                'anonymous' => empty($r->respondent_name),
            ]);

        return compact(
            'totalSurveys', 'activeSurveys', 'totalResponses',
            'thisWeek', 'thisMonth', 'completionRate',
            'dailyLabels', 'dailyCounts',
            'surveys', 'surveyLabels', 'surveyCounts',
            'npsScore', 'npsBreakdown',
            'topColours', 'avgRating',
            'recentResponses'
        );
    }
}

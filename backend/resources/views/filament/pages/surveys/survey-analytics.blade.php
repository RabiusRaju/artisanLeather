<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- ══ ROW 1 — KPI Cards ════════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">📋 Total Surveys</div>
        <div style="font-size:36px;font-weight:900;color:#2563eb;line-height:1">{{ $d['totalSurveys'] }}</div>
        <div style="font-size:12px;color:#059669;margin-top:8px;font-weight:600">🟢 {{ $d['activeSurveys'] }} active</div>
    </div>

    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">✅ Total Responses</div>
        <div style="font-size:36px;font-weight:900;color:#059669;line-height:1">{{ number_format($d['totalResponses']) }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:8px">{{ $d['completionRate'] }}% completion rate</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fde68a;background:linear-gradient(135deg,#fffbeb,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">📅 This Month</div>
        <div style="font-size:36px;font-weight:900;color:#d97706;line-height:1">{{ $d['thisMonth'] }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:8px">{{ $d['thisWeek'] }} this week</div>
    </div>

    @if($d['npsScore'] !== null)
    @php
        $npsColor = $d['npsScore'] >= 50 ? '#059669' : ($d['npsScore'] >= 0 ? '#d97706' : '#dc2626');
        $npsLabel = $d['npsScore'] >= 50 ? 'Excellent' : ($d['npsScore'] >= 0 ? 'Good' : 'Needs Work');
    @endphp
    <div style="border-radius:14px;border:1px solid #e9d5ff;background:linear-gradient(135deg,#faf5ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">📊 NPS Score</div>
        <div style="font-size:36px;font-weight:900;color:{{ $npsColor }};line-height:1">{{ $d['npsScore'] }}</div>
        <div style="font-size:12px;font-weight:600;color:{{ $npsColor }};margin-top:8px">{{ $npsLabel }}</div>
    </div>
    @endif

    @if($d['avgRating'] !== null)
    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">⭐ Avg Rating</div>
        <div style="font-size:36px;font-weight:900;color:#f59e0b;line-height:1">{{ $d['avgRating'] }}</div>
        <div style="font-size:12px;color:#6b7280;margin-top:8px">out of 5 stars</div>
    </div>
    @endif

    <div style="border-radius:14px;border:1px solid #bfdbfe;background:linear-gradient(135deg,#f0f9ff,#fff);padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">🎯 Completion Rate</div>
        <div style="font-size:36px;font-weight:900;color:#0284c7;line-height:1">{{ $d['completionRate'] }}%</div>
        <div style="height:6px;background:#e5e7eb;border-radius:99px;overflow:hidden;margin-top:12px">
            <div style="height:100%;width:{{ $d['completionRate'] }}%;background:#0284c7;border-radius:99px"></div>
        </div>
    </div>

</div>

{{-- ══ ROW 2 — Daily Trend + Per Survey Bar ═════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px">

    {{-- Daily Responses Line Chart --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📈 Daily Responses — Last 30 Days</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Completed responses per day across all active surveys</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const counts = {{ json_encode($d['dailyCounts']) }};
                const maxVal = Math.max(...counts) || 1;
                el._ci = new Chart(el, {
                    type: 'line',
                    data: {
                        labels: {{ json_encode($d['dailyLabels']) }},
                        datasets: [{
                            label: 'Responses',
                            data: counts,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245,158,11,0.1)',
                            borderWidth: 2.5,
                            pointRadius: counts.map(v => v > 0 ? 4 : 0),
                            pointBackgroundColor: '#f59e0b',
                            fill: true,
                            tension: 0.4,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: c => c.parsed.y + ' responses' } }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 9 } } },
                            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } }
                        }
                    }
                });
             })"
             style="position:relative;height:220px">
            <canvas></canvas>
        </div>
    </div>

    {{-- Responses per Survey Bar --}}
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📋 Responses per Survey</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Total completed per survey</div>
        <div x-data="{}"
             x-init="$nextTick(() => {
                const el = $el.querySelector('canvas');
                if (!el || !window.Chart) return;
                if (el._ci) el._ci.destroy();
                const counts = {{ json_encode($d['surveyCounts']) }};
                const maxV = Math.max(...counts) || 1;
                el._ci = new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: {{ json_encode($d['surveyLabels']) }},
                        datasets: [{
                            data: counts,
                            backgroundColor: counts.map(v => `rgba(245,158,11,${(0.4+v/maxV*0.55).toFixed(2)})`),
                            borderColor: '#f59e0b',
                            borderWidth: 1.5,
                            borderRadius: { topRight: 6, bottomRight: 6 },
                            borderSkipped: 'left',
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.parsed.x + ' responses' } } },
                        scales: {
                            x: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
                            y: { grid: { display: false }, ticks: { font: { size: 10 } } }
                        }
                    }
                });
             })"
             style="position:relative;height:220px">
            <canvas></canvas>
        </div>
    </div>

</div>

{{-- ══ ROW 3 — NPS Breakdown + Top Colour Preferences ══════════════════ --}}
@if($d['npsScore'] !== null || !empty($d['topColours']))
<div style="display:grid;grid-template-columns:{{ $d['npsScore'] !== null && !empty($d['topColours']) ? '1fr 1fr' : '1fr' }};gap:16px;margin-bottom:16px">

    {{-- NPS Breakdown --}}
    @if($d['npsScore'] !== null)
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 NPS Breakdown</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Net Promoter Score: 9–10 = Promoters · 7–8 = Passives · 0–6 = Detractors</div>

        @php
            $b = $d['npsBreakdown'];
            $total = $b['total'] ?: 1;
        @endphp

        {{-- Big NPS number --}}
        <div style="text-align:center;padding:16px 0;margin-bottom:16px">
            <div style="font-size:56px;font-weight:900;color:{{ $d['npsScore'] >= 50 ? '#059669' : ($d['npsScore'] >= 0 ? '#d97706' : '#dc2626') }};line-height:1">
                {{ $d['npsScore'] >= 0 ? '+' : '' }}{{ $d['npsScore'] }}
            </div>
            <div style="font-size:12px;color:#9ca3af;margin-top:6px">NPS Score · {{ $b['total'] }} respondents</div>
        </div>

        {{-- Segment bars --}}
        @foreach([['Promoters','#22c55e',$b['promoters']],['Passives','#f59e0b',$b['passives']],['Detractors','#ef4444',$b['detractors']]] as [$label,$color,$count])
        <div style="margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                <span style="color:#374151;font-weight:500">{{ $label }}</span>
                <span style="color:{{ $color }};font-weight:700">{{ $count }} ({{ $total > 0 ? round(($count/$total)*100) : 0 }}%)</span>
            </div>
            <div style="height:8px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                <div style="height:100%;width:{{ $total > 0 ? round(($count/$total)*100) : 0 }}%;background:{{ $color }};border-radius:99px"></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Top Colour Preferences --}}
    @if(!empty($d['topColours']))
    <div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">🎨 Top Colour Preferences</div>
        <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Most chosen leather colours across product surveys</div>
        @php
            $maxVotes = max($d['topColours']) ?: 1;
            $palette = ['#f59e0b','#92400e','#1f2937','#6b7280','#7c3aed'];
        @endphp
        <div style="display:flex;flex-direction:column;gap:12px">
            @foreach($d['topColours'] as $colour => $votes)
            @php $i = $loop->index; $pct = round(($votes/$maxVotes)*100); @endphp
            <div>
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:5px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:10px;height:10px;border-radius:50%;background:{{ $palette[$i % 5] }};flex-shrink:0"></div>
                        <span style="color:#374151;font-weight:500">{{ $colour }}</span>
                    </div>
                    <span style="color:#d97706;font-weight:700">{{ $votes }} votes</span>
                </div>
                <div style="height:8px;background:#f3f4f6;border-radius:99px;overflow:hidden">
                    <div style="height:100%;width:{{ $pct }}%;background:{{ $palette[$i % 5] }};border-radius:99px"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endif

{{-- ══ ROW 4 — Active Survey Cards ══════════════════════════════════════ --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div>
            <div style="font-size:14px;font-weight:700;color:#111827">📋 Survey Performance</div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">Response rate and completion per survey</div>
        </div>
        <a href="/admin/surveys/create"
           style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:6px 14px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb">
            + New Survey
        </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;divide-x:1px solid #f3f4f6">
        @foreach($d['surveys'] as $survey)
        @php
            $statusColor = match($survey['status']) { 'active' => '#059669', 'closed' => '#dc2626', default => '#9ca3af' };
            $statusLabel = match($survey['status']) { 'active' => '🟢 Active', 'closed' => '🔴 Closed', default => '📝 Draft' };
        @endphp
        <div style="padding:20px;border-right:1px solid #f3f4f6;{{ $loop->last ? 'border-right:none' : '' }}">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px">
                <div style="font-size:13px;font-weight:600;color:#111827;line-height:1.3;max-width:75%">{{ $survey['title'] }}</div>
                <span style="font-size:10px;font-weight:600;color:{{ $statusColor }};white-space:nowrap;margin-left:8px">{{ $statusLabel }}</span>
            </div>

            {{-- Stats row --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px">
                <div style="text-align:center;padding:8px;background:#f9fafb;border-radius:8px">
                    <div style="font-size:20px;font-weight:900;color:#2563eb">{{ $survey['completed'] }}</div>
                    <div style="font-size:9px;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-top:2px">Responses</div>
                </div>
                <div style="text-align:center;padding:8px;background:#f9fafb;border-radius:8px">
                    <div style="font-size:20px;font-weight:900;color:#d97706">{{ $survey['completion_rate'] }}%</div>
                    <div style="font-size:9px;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-top:2px">Complete</div>
                </div>
                <div style="text-align:center;padding:8px;background:#f9fafb;border-radius:8px">
                    <div style="font-size:20px;font-weight:900;color:#7c3aed">{{ $survey['questions_count'] }}</div>
                    <div style="font-size:9px;color:#9ca3af;text-transform:uppercase;letter-spacing:.04em;margin-top:2px">Questions</div>
                </div>
            </div>

            {{-- Completion rate bar --}}
            <div style="height:4px;background:#f3f4f6;border-radius:99px;overflow:hidden;margin-bottom:12px">
                <div style="height:100%;width:{{ $survey['completion_rate'] }}%;background:{{ $survey['completion_rate'] >= 80 ? '#22c55e' : ($survey['completion_rate'] >= 50 ? '#f59e0b' : '#ef4444') }};border-radius:99px"></div>
            </div>

            <div style="font-size:10px;color:#9ca3af;margin-bottom:12px">Last response: {{ $survey['latest'] }}</div>

            {{-- Action buttons --}}
            <div style="display:flex;gap:6px">
                <a href="/admin/surveys/{{ $survey['id'] }}/edit?tab=analytics%3A%3Adata%3A%3Atab"
                   style="flex:1;text-align:center;padding:6px;font-size:10px;font-weight:600;color:#2563eb;text-decoration:none;background:#eff6ff;border-radius:6px;border:1px solid #bfdbfe">
                    📊 Analytics
                </a>
                @if($survey['status'] === 'active')
                <a href="{{ 'https://wa.me/?text=' . urlencode("Hi! 😊\n\nWe'd love your feedback. Please take 2 minutes:\n\n📋 *{$survey['title']}*\n\n👉 {$survey['url']}\n\nThank you! 🌟") }}"
                   target="_blank"
                   style="flex:1;text-align:center;padding:6px;font-size:10px;font-weight:600;color:#fff;text-decoration:none;background:#25d366;border-radius:6px">
                    📱 Share
                </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ══ ROW 5 — Recent Responses ══════════════════════════════════════════ --}}
@if($d['recentResponses']->count() > 0)
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;background:#f9fafb">
        <div style="font-size:14px;font-weight:700;color:#111827">🕐 Recent Responses</div>
        <div style="font-size:11px;color:#9ca3af;margin-top:2px">Latest completed submissions</div>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Survey</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Country</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Submitted</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Anonymous</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['recentResponses'] as $r)
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:13px;color:#374151;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $r['survey'] }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#6b7280">{{ $r['country'] ?: '—' }}</td>
                    <td style="padding:12px 16px;font-size:12px;color:#9ca3af;white-space:nowrap">{{ $r['completed'] }}</td>
                    <td style="padding:12px 16px;text-align:center;font-size:14px">{{ $r['anonymous'] ? '🔒' : '👤' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</x-filament-panels::page>

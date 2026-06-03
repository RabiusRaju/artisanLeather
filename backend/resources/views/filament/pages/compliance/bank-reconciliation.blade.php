<x-filament-panels::page>
<script src="/js/chart.umd.min.js"></script>
@php $d = $this->getData(); @endphp

{{-- KPI Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">

    <div style="border-radius:14px;border:1px solid #a7f3d0;background:linear-gradient(135deg,#ecfdf5,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">💚 Total Cash In</div>
        <div style="font-size:22px;font-weight:900;color:#059669;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalIn'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">All inflows recorded</div>
    </div>

    <div style="border-radius:14px;border:1px solid #fecaca;background:linear-gradient(135deg,#fef2f2,#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">🔴 Total Cash Out</div>
        <div style="font-size:22px;font-weight:900;color:#dc2626;font-variant-numeric:tabular-nums">OMR {{ number_format($d['totalOut'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">All outflows recorded</div>
    </div>

    <div style="border-radius:14px;border:1px solid {{ $d['netBalance']>=0?'#a7f3d0':'#fecaca' }};background:linear-gradient(135deg,{{ $d['netBalance']>=0?'#ecfdf5':'#fef2f2' }},#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">{{ $d['netBalance']>=0?'💰':'⚠️' }} Net Balance</div>
        <div style="font-size:22px;font-weight:900;color:{{ $d['netBalance']>=0?'#059669':'#dc2626' }};font-variant-numeric:tabular-nums">OMR {{ number_format($d['netBalance'],3) }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">Cash In – Cash Out</div>
    </div>

    <div style="border-radius:14px;border:1px solid {{ $d['unreconciledEntries']->count()>0?'#fde68a':'#a7f3d0' }};background:linear-gradient(135deg,{{ $d['unreconciledEntries']->count()>0?'#fffbeb':'#ecfdf5' }},#fff);padding:18px 20px;box-shadow:0 1px 4px rgba(0,0,0,.06)">
        <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;color:#6b7280;margin-bottom:10px">{{ $d['unreconciledEntries']->count()>0?'⏳':'✅' }} Unreconciled</div>
        <div style="font-size:32px;font-weight:900;color:{{ $d['unreconciledEntries']->count()>0?'#d97706':'#059669' }}">{{ $d['unreconciledEntries']->count() }}</div>
        <div style="font-size:11px;color:#6b7280;margin-top:6px">{{ $d['unreconciledEntries']->count()>0?'Entries need review':'All entries matched' }}</div>
    </div>

</div>

{{-- Cash Flow Chart --}}
<div style="border-radius:14px;border:1px solid #e5e7eb;background:#fff;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:16px">
    <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:4px">📊 Cash Flow Summary</div>
    <div style="font-size:11px;color:#9ca3af;margin-bottom:16px">Inflows vs outflows vs net balance</div>
    <div x-data="{}"
         x-init="$nextTick(() => {
            const el = $el.querySelector('canvas');
            if (!el || !window.Chart) return;
            if (el._ci) { el._ci.destroy(); el._ci = null; }
            el._ci = new Chart(el, {
                type:'bar',
                data:{
                    labels:['Cash In','Cash Out','Net Balance'],
                    datasets:[{
                        data:[{{ $d['totalIn'] }},{{ $d['totalOut'] }},{{ $d['netBalance'] }}],
                        backgroundColor:['rgba(34,197,94,.8)','rgba(239,68,68,.8)',{{ $d['netBalance']>=0?'rgba(16,185,129,.8)':'rgba(239,68,68,.8)' }}],
                        borderColor:['#22c55e','#ef4444',{{ $d['netBalance']>=0?'#10b981':'#ef4444' }}],
                        borderWidth:2,borderRadius:10
                    }]
                },
                options:{
                    responsive:true,maintainAspectRatio:false,
                    plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>'OMR '+c.parsed.y.toFixed(3)}}},
                    scales:{
                        x:{grid:{display:false},ticks:{font:{size:13,weight:'600'}}},
                        y:{beginAtZero:true,ticks:{callback:v=>'OMR '+v.toFixed(0)},grid:{color:'rgba(0,0,0,.05)'}}
                    }
                }
            });
         })"
         style="position:relative;height:180px"><canvas></canvas></div>
</div>

{{-- Status Summary --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
    <div style="border-radius:12px;padding:16px 20px;background:linear-gradient(135deg,#ecfdf5,#fff);border:1px solid #a7f3d0;display:flex;align-items:center;gap:14px">
        <div style="width:44px;height:44px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px">✅</div>
        <div>
            <div style="font-size:13px;font-weight:700;color:#065f46">Reconciled Entries</div>
            <div style="font-size:20px;font-weight:900;color:#059669">OMR {{ number_format($d['reconciledIn'],3) }}</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">Matched with bank statement</div>
        </div>
    </div>
    <div style="border-radius:12px;padding:16px 20px;background:linear-gradient(135deg,#fffbeb,#fff);border:1px solid #fde68a;display:flex;align-items:center;gap:14px">
        <div style="width:44px;height:44px;border-radius:50%;background:#f59e0b;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:20px">⏳</div>
        <div>
            <div style="font-size:13px;font-weight:700;color:#92400e">Pending Review</div>
            <div style="font-size:20px;font-weight:900;color:#d97706">{{ $d['unreconciledEntries']->count() }} entries</div>
            <div style="font-size:11px;color:#6b7280;margin-top:2px">Verify against bank statement</div>
        </div>
    </div>
</div>

{{-- Unreconciled Entries Table --}}
<div style="border-radius:14px;border:1px solid {{ $d['unreconciledEntries']->count()>0?'#fde68a':'#a7f3d0' }};background:#fff;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.06)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #f3f4f6;background:{{ $d['unreconciledEntries']->count()>0?'#fffbeb':'#ecfdf5' }}">
        <div>
            <div style="font-size:14px;font-weight:700;color:{{ $d['unreconciledEntries']->count()>0?'#92400e':'#065f46' }}">
                {{ $d['unreconciledEntries']->count()>0?'⏳ Unreconciled Entries — verify against bank statement':'✅ All Entries Reconciled' }}
            </div>
            <div style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $d['unreconciledEntries']->count()>0?'Click ✓ Reconcile when confirmed with your bank statement':'Your records match the bank statement' }}</div>
        </div>
        <a href="/admin/operations/cash-flow/cash-flows/create"
           style="font-size:12px;font-weight:600;color:#f59e0b;text-decoration:none;padding:6px 14px;border:1px solid #fde68a;border-radius:8px;background:#fffbeb">
            + Log Entry
        </a>
    </div>

    @if($d['unreconciledEntries']->isEmpty())
    <div style="display:flex;flex-direction:column;align-items:center;padding:60px 20px;color:#9ca3af">
        <span style="font-size:48px;margin-bottom:12px">🎉</span>
        <div style="font-size:16px;font-weight:700;color:#059669;margin-bottom:6px">All entries reconciled!</div>
        <div style="font-size:13px;color:#9ca3af">Your books match the bank statement perfectly.</div>
    </div>
    @else
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f9fafb">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;white-space:nowrap">Date</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Flow</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Description</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Category</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Method</th>
                    <th style="padding:10px 16px;text-align:right;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Amount</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#6b7280">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($d['unreconciledEntries'] as $e)
                <tr style="border-top:1px solid #f3f4f6" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                    <td style="padding:12px 16px;font-size:12px;color:#6b7280;white-space:nowrap">{{ $e->entry_date->format('d M Y') }}</td>
                    <td style="padding:12px 16px">
                        <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:99px;background:{{ $e->type==='in'?'#d1fae5':'#fee2e2' }};color:{{ $e->type==='in'?'#065f46':'#991b1b' }}">
                            {{ $e->type==='in'?'↓ IN':'↑ OUT' }}
                        </span>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;color:#374151;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $e->description }}</td>
                    <td style="padding:12px 16px">
                        <span style="font-size:11px;padding:2px 8px;border-radius:99px;background:#f3f4f6;color:#374151;white-space:nowrap">{{ ucfirst(str_replace('_',' ',$e->category)) }}</span>
                    </td>
                    <td style="padding:12px 16px;font-size:12px;color:#6b7280">{{ ucfirst(str_replace('_',' ',$e->payment_method)) }}</td>
                    <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:700;color:{{ $e->type==='in'?'#059669':'#dc2626' }};font-variant-numeric:tabular-nums;white-space:nowrap">
                        {{ $e->type==='in'?'+':'-' }} OMR {{ number_format($e->amount_omr,3) }}
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <button wire:click="markReconciled({{ $e->id }})"
                            style="font-size:12px;font-weight:600;padding:6px 16px;border-radius:8px;border:none;cursor:pointer;background:#d1fae5;color:#065f46;transition:all .15s"
                            onmouseover="this.style.background='#a7f3d0'" onmouseout="this.style.background='#d1fae5'">
                            ✓ Reconcile
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Help note --}}
<div style="margin-top:12px;padding:12px 18px;border-radius:10px;background:#eff6ff;border:1px solid #bfdbfe;font-size:11px;color:#1e40af;display:flex;align-items:flex-start;gap:8px">
    <span style="font-size:16px;flex-shrink:0">💡</span>
    <span>Compare each entry against your <strong>Bank Muscat statement</strong>. Click <strong>✓ Reconcile</strong> when an entry matches a line on the bank statement. Reconciled entries are locked and won't appear here again.</span>
</div>

</x-filament-panels::page>

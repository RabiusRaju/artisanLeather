@php
use Spatie\Activitylog\Models\Activity;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Resources\Blog\PostResource;
use App\Filament\Resources\Customers\CustomerResource;

$resourceMap = [
    \App\Models\Order::class    => OrderResource::class,
    \App\Models\Product::class  => ProductResource::class,
    \App\Models\Post::class     => PostResource::class,
    \App\Models\Customer::class => CustomerResource::class,
];

$activities = Activity::with('subject')->latest()->limit(7)->get();

$resolveUrl = function (Activity $activity) use ($resourceMap) {
    if (! $activity->subject) {
        return null;
    }

    $resourceClass = $resourceMap[$activity->subject_type] ?? null;

    if (! $resourceClass || ! $resourceClass::hasPage('edit')) {
        return null;
    }

    try {
        return $resourceClass::getUrl('edit', ['record' => $activity->subject]);
    } catch (\Throwable) {
        return null;
    }
};
@endphp

<div x-data="{ open: false }" x-on:click.outside="open = false" style="position:relative">

    <button
        x-on:click="open = ! open"
        title="Recent activity"
        style="position:relative;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;color:#9ca3af;background:transparent;transition:background .2s;border:none;cursor:pointer"
        onmouseover="this.style.background='rgba(0,0,0,0.05)'"
        onmouseout="this.style.background='transparent'"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:20px;height:20px">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition
        style="position:absolute;right:0;top:44px;width:340px;max-height:420px;overflow-y:auto;background:#fff;border:1px solid rgba(0,0,0,0.08);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.12);z-index:50"
    >
        <div style="padding:12px 16px;border-bottom:1px solid rgba(0,0,0,0.06);font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.04em">
            Recent Activity
        </div>

        @forelse ($activities as $activity)
            @php $url = $resolveUrl($activity); @endphp
            <{{ $url ? 'a' : 'div' }}
                @if ($url) href="{{ $url }}" @endif
                style="display:block;padding:10px 16px;text-decoration:none;border-bottom:1px solid rgba(0,0,0,0.04);transition:background .15s"
                @if ($url)
                    onmouseover="this.style.background='rgba(0,0,0,0.03)'"
                    onmouseout="this.style.background='transparent'"
                @endif
            >
                <div style="font-size:13px;color:#1f2937;line-height:1.4">{{ $activity->description }}</div>
                <div style="font-size:11px;color:#9ca3af;margin-top:2px">
                    {{ $activity->causer?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}
                </div>
            </{{ $url ? 'a' : 'div' }}>
        @empty
            <div style="padding:20px 16px;font-size:13px;color:#9ca3af;text-align:center">
                No activity yet.
            </div>
        @endforelse
    </div>
</div>

{{-- New Notification Card --}}
@php
    $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
        ->where('is_read', false)
        ->count();

    $recentNotifications = \App\Models\Notification::where('user_id', auth()->id())
        ->where('is_read', false)
        ->latest()
        ->take(2)
        ->get();
@endphp

@if($recentNotifications->count() > 0)
<x-ui.card class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800" style="display:flex;align-items:center;gap:8px;">
            New Notification
            <span id="notif-unread-badge" style="display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 6px;font-size:11px;font-weight:700;color:#fff;background:#ef4444;border-radius:9999px;">
                {{ $unreadCount }}
            </span>
        </h3>
        <a href="{{ route('notifications.inbox') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
            View All →
        </a>
    </div>

    <div class="space-y-3" id="notif-card-items">
        @foreach($recentNotifications as $notif)
            <div class="flex items-start gap-3 {{ !$notif->is_read ? 'bg-indigo-50/50 -mx-2 px-2 py-2 rounded-lg' : 'py-1' }}">
                {{-- Type icon --}}
                <div class="flex-shrink-0 mt-0.5">
                    @switch($notif->type)
                        @case('success')
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-600 text-xs">✓</span>
                            @break
                        @case('error')
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-600 text-xs">✕</span>
                            @break
                        @case('warning')
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 text-xs">⚠</span>
                            @break
                        @default
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-600 text-xs">ℹ</span>
                    @endswitch
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $notif->title }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $notif->message }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                        @if($notif->link)
                            <a href="{{ $notif->link }}" class="text-[10px] text-indigo-600 hover:underline font-medium">View →</a>
                        @endif
                    </div>
                </div>

                {{-- Delete --}}
                <form method="POST" action="{{ route('notifications.delete', $notif) }}" class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button class="text-gray-300 hover:text-red-500 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
</x-ui.card>

<script>
(function() {
    const badge = document.getElementById('notif-unread-badge');
    if (!badge) return;

    const typeIcons = {
        success: '<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-green-100 text-green-600 text-xs">✓</span>',
        error:   '<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-red-100 text-red-600 text-xs">✕</span>',
        warning: '<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 text-xs">⚠</span>',
        info:    '<span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-600 text-xs">ℹ</span>',
    };

    async function pollNotifCard() {
        try {
            const resp = await fetch('/notifications/poll');
            if (!resp.ok) return;
            const data = await resp.json();

            // Update badge count
            if (badge.textContent.trim() !== String(data.unread_count)) {
                badge.textContent = data.unread_count;
                badge.style.transition = 'transform .3s';
                badge.style.transform = 'scale(1.3)';
                setTimeout(() => badge.style.transform = 'scale(1)', 400);
            }

            // Update notification items (show latest 2)
            const container = document.getElementById('notif-card-items');
            if (container && data.notifications && data.notifications.length > 0) {
                const latest2 = data.notifications.slice(0, 2);
                container.innerHTML = latest2.map(n => `
                    <div class="flex items-start gap-3 bg-indigo-50/50 -mx-2 px-2 py-2 rounded-lg">
                        <div class="flex-shrink-0 mt-0.5">${typeIcons[n.type] || typeIcons.info}</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">${n.title}</p>
                            <p class="text-xs text-gray-500 truncate">${n.message}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span style="font-size:10px;color:#9ca3af;">${n.time}</span>
                                ${n.link ? `<a href="${n.link}" style="font-size:10px;color:#4f46e5;font-weight:500;">View →</a>` : ''}
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        } catch (e) {}
    }
    setInterval(pollNotifCard, 5000);
})();
</script>
@endif

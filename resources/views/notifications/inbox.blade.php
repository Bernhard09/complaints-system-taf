<x-app-layout>
    <x-slot name="header">
        Inbox
    </x-slot>

    <div class="mx-auto w-full max-w-screen-xl space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Inbox</h2>
                <p class="text-sm text-gray-500 mt-1" id="inbox-subtitle">
                    {{ $notifications->total() }} notification{{ $notifications->total() !== 1 ? 's' : '' }}
                    @if($unreadCount > 0)
                        · <span class="text-indigo-600 font-medium" id="inbox-unread-label">{{ $unreadCount }} unread</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3">
                @if($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.markAllRead') }}">
                        @csrf
                        <button class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                            Mark all as read
                        </button>
                    </form>
                @endif

                @if($notifications->total() > 0)
                    <form method="POST" action="{{ route('notifications.clearAll') }}" onsubmit="return confirm('Delete all notifications?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-4 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition">
                            Clear all
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Notification list --}}
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden" id="inbox-list">
            @forelse($notifications as $notif)
                <div class="flex items-start gap-4 px-6 py-4 border-b last:border-b-0 hover:bg-gray-50 transition
                    {{ !$notif->is_read ? 'bg-indigo-50/40' : '' }}" data-notif-id="{{ $notif->id }}">

                    {{-- Icon --}}
                    <div class="flex-shrink-0 mt-1">
                        @switch($notif->type)
                            @case('success')
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-100 text-green-600 text-sm font-bold">✓</span>
                                @break
                            @case('error')
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-red-100 text-red-600 text-sm font-bold">✕</span>
                                @break
                            @case('warning')
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-100 text-amber-600 text-sm font-bold">⚠</span>
                                @break
                            @default
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-100 text-blue-600 text-sm font-bold">ℹ</span>
                        @endswitch
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800">{{ $notif->title }}</p>
                            @if(!$notif->is_read)
                                <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $notif->message }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->format('d M Y, H:i') }} · {{ $notif->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex-shrink-0 flex items-center gap-2 mt-1">
                        @if($notif->link)
                            <a href="{{ $notif->link }}"
                               class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                View
                            </a>
                        @endif

                        @if(!$notif->is_read)
                            <form method="POST" action="{{ route('notifications.markRead', $notif) }}">
                                @csrf
                                <button class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition"
                                        title="Mark as read">
                                    ✓ Read
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('notifications.delete', $notif) }}">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1.5 text-xs font-medium text-red-500 bg-red-50 hover:bg-red-100 rounded-lg transition"
                                    title="Delete">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center" id="inbox-empty">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-400 text-2xl mb-4">
                        🔔
                    </div>
                    <p class="text-gray-500 font-medium">No notifications yet</p>
                    <p class="text-gray-400 text-sm mt-1">You'll see notifications here when there's activity.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($notifications->hasPages())
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>

    {{-- Async polling for new notifications --}}
    <script>
    (function() {
        let lastId = 0;
        // Find highest existing notification ID
        document.querySelectorAll('[data-notif-id]').forEach(el => {
            const id = parseInt(el.dataset.notifId);
            if (id > lastId) lastId = id;
        });

        const typeIcons = {
            success: '<span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-green-100 text-green-600 text-sm font-bold">✓</span>',
            error:   '<span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-red-100 text-red-600 text-sm font-bold">✕</span>',
            warning: '<span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-amber-100 text-amber-600 text-sm font-bold">⚠</span>',
            info:    '<span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-blue-100 text-blue-600 text-sm font-bold">ℹ</span>',
        };

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function buildNotifRow(n) {
            return `<div class="flex items-start gap-4 px-6 py-4 border-b last:border-b-0 hover:bg-gray-50 transition bg-indigo-50/40" data-notif-id="${n.id}" style="animation: fadeIn .3s ease;">
                <div class="flex-shrink-0 mt-1">${typeIcons[n.type] || typeIcons.info}</div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-semibold text-gray-800">${escapeHtml(n.title)}</p>
                        <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                    </div>
                    <p class="text-sm text-gray-600 mt-0.5">${escapeHtml(n.message)}</p>
                    <p class="text-xs text-gray-400 mt-1">${n.time}</p>
                </div>
                <div class="flex-shrink-0 flex items-center gap-2 mt-1">
                    ${n.link ? `<a href="${n.link}" class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">View</a>` : ''}
                </div>
            </div>`;
        }

        async function pollInbox() {
            try {
                const resp = await fetch(`/notifications/poll?last_id=${lastId}`);
                if (!resp.ok) return;
                const data = await resp.json();

                // Update unread label
                const unreadLabel = document.getElementById('inbox-unread-label');
                if (unreadLabel) {
                    unreadLabel.textContent = data.unread_count + ' unread';
                }

                if (data.notifications && data.notifications.length > 0) {
                    const list = document.getElementById('inbox-list');
                    const emptyState = document.getElementById('inbox-empty');
                    if (emptyState) emptyState.remove();

                    // Prepend new notifications
                    data.notifications.forEach(n => {
                        if (n.id <= lastId) return;

                        const temp = document.createElement('div');
                        temp.innerHTML = buildNotifRow(n);
                        const row = temp.firstElementChild;
                        list.prepend(row);

                        // Show toast
                        if (window.__showToast) {
                            window.__showToast(n.type || 'info', n.title, n.message);
                        }
                    });

                    const maxId = Math.max(...data.notifications.map(n => n.id));
                    if (maxId > lastId) lastId = maxId;
                }
            } catch (e) {}
        }
        setInterval(pollInbox, 5000);
    })();
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</x-app-layout>


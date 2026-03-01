<x-app-layout>
    <x-slot name="header">
        Inbox
    </x-slot>

    <div class="mx-auto w-full max-w-screen-xl px-8 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Inbox</h2>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $notifications->total() }} notification{{ $notifications->total() !== 1 ? 's' : '' }}
                    @if($unreadCount > 0)
                        · <span class="text-indigo-600 font-medium">{{ $unreadCount }} unread</span>
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
        <div class="bg-white rounded-2xl shadow-sm border overflow-hidden">
            @forelse($notifications as $notif)
                <div class="flex items-start gap-4 px-6 py-4 border-b last:border-b-0 hover:bg-gray-50 transition
                    {{ !$notif->is_read ? 'bg-indigo-50/40' : '' }}">

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
                <div class="px-6 py-16 text-center">
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
</x-app-layout>

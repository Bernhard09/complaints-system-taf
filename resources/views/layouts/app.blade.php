<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="h-screen overflow-hidden bg-gray-100">

{{-- Toast container — top-right corner --}}
<div id="toast-container" style="position:fixed; top:20px; right:20px; z-index:9999; width:320px; display:flex; flex-direction:column; gap:10px;"></div>

{{-- Show server-side flash toasts on page load --}}
@if(session('success') || session('error') || session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.__showToast(
            '{{ session('success') ? 'success' : (session('error') ? 'error' : 'warning') }}',
            '{{ session('success') ? 'Success' : (session('error') ? 'Error' : 'Warning') }}',
            @json(session('success') ?? session('error') ?? session('warning'))
        );
    });
</script>
@endif

<div x-data="{ collapsed: $persist(false), mobileOpen: false }"
     class="flex h-screen overflow-hidden">

    {{-- MOBILE OVERLAY --}}
    <div x-show="mobileOpen"
            class="fixed inset-0 bg-black/40 z-40 md:hidden"
            @click="mobileOpen = false">
    </div>

    {{-- SIDEBAR --}}
    <aside
        :class="collapsed ? 'w-20' : 'w-64'"
        class="bg-white border-r border-gray-200
                transition-all duration-300
                h-screen
                hidden md:flex md:flex-col">

        @include('layouts.sidebar')
    </aside>

    {{-- MOBILE SIDEBAR --}}
    <aside x-show="mobileOpen"
           class="fixed inset-y-0 left-0 w-64 bg-white border-r z-50 md:hidden">

        @include('layouts.sidebar')
    </aside>

    {{-- MAIN --}}
    <div class="flex-1 flex flex-col min-w-0">

        <header class="bg-white border-b px-4 sm:px-6 py-4 flex items-center justify-between">

            <div class="flex items-center gap-2 md:gap-4 flex-1">

                <button class="md:hidden p-2 text-gray-600 hover:text-indigo-600"
                        @click="mobileOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <button class="hidden md:block p-2 text-gray-400 hover:text-indigo-600 transition"
                        @click="collapsed = !collapsed">
                    ⇔
                </button>

                <h1 class="text-base md:text-lg font-semibold text-gray-800 truncate" style="max-width: 150px;">
                    @if (isset($header))
                        {{ $header }}
                    @elseif (isset($pageTitle))
                        {{ $pageTitle }}
                    @else
                        {{ config('app.name') }}
                    @endif
                </h1>

            </div>

            {{-- Language Switcher & Notification Bell --}}
            <div class="flex items-center gap-1 md:gap-4">

                {{-- Language Switcher Dropdown --}}
                @auth
                    @php
                        $currentLocale = app()->getLocale();
                    @endphp
                    <div x-data="{ langOpen: false }" class="relative">
                        <button @click="langOpen = !langOpen"
                                class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-indigo-600 transition px-2 py-1.5 rounded-lg hover:bg-gray-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="uppercase">{{ $currentLocale }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <div x-show="langOpen"
                             @click.outside="langOpen = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-32 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden">
                            <div class="py-1 flex flex-col">
                                <a href="{{ route('lang.switch', 'id') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-indigo-600 flex items-center justify-between {{ $currentLocale === 'id' ? 'font-bold bg-indigo-50' : '' }}">
                                    Indonesia
                                    @if($currentLocale === 'id')<span class="text-indigo-600">✓</span>@endif
                                </a>
                                <a href="{{ route('lang.switch', 'en') }}" class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-indigo-600 flex items-center justify-between {{ $currentLocale === 'en' ? 'font-bold bg-indigo-50' : '' }}">
                                    English
                                    @if($currentLocale === 'en')<span class="text-indigo-600">✓</span>@endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Bell icon + Notification dropdown --}}
                @auth
                    @php
                        $initialUnreadCount = \App\Models\Notification::where('user_id', auth()->id())
                            ->where('is_read', false)
                            ->count();
                    @endphp
                    <div x-data="notificationBell({{ $initialUnreadCount }})" x-init="init()" class="relative">
                        <button @click="open = !open"
                                class="relative text-gray-700 hover:text-indigo-600 transition p-2 rounded-lg hover:bg-gray-100">
                            {{-- Filled bell icon --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-6 h-6">
                                <path fill="currentColor" d="M5.85 17.1q-.375 0-.613-.237T5 16.25q0-.375.238-.613t.612-.237h.9V9.6q0-1.825 1.088-3.2T10.85 4.85v-.575q0-.475.338-.812T12 3.125q.475 0 .813.338t.337.812v.575q1.925.475 3.013 1.85t1.087 3.2v5.5h.9q.375 0 .613.238t.237.612q0 .375-.237.613t-.613.237H5.85ZM12 20.9q-.825 0-1.413-.587T10 18.9h4q0 .825-.587 1.413T12 20.9Z"/>
                            </svg>
                            {{-- Red dot for unread notifications --}}
                            <span x-show="unreadCount > 0"
                                  class="absolute top-1.5 right-1.5 w-3 h-3 bg-red-500 rounded-full border-2 border-white">
                            </span>
                        </button>


                    {{-- Dropdown panel --}}
                    <div x-show="open"
                         @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-96 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 overflow-hidden">

                        {{-- Header --}}
                        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-sm text-gray-700">{{ __('Notifications') }}</h3>
                                <span x-show="unreadCount > 0"
                                      x-text="unreadCount"
                                      class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="unreadCount > 0">
                                    <form method="POST" action="{{ route('notifications.markAllRead') }}">
                                        @csrf
                                        <button class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                            {{ __('Mark all read') }}
                                        </button>
                                    </form>
                                </template>
                                <form method="POST" action="{{ route('notifications.clearAll') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-xs text-red-500 hover:text-red-700 font-medium">
                                        {{ __('Clear') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Notification list --}}
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                            <template x-if="items.length === 0">
                                <div class="px-4 py-8 text-center">
                                    <p class="text-gray-400 text-sm">{{ __('No new notifications') }}</p>
                                </div>
                            </template>
                            <template x-for="n in items" :key="n.id">
                                <div class="px-4 py-3 hover:bg-gray-50 transition flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <span :class="{
                                            'bg-green-100 text-green-600': n.type === 'success',
                                            'bg-red-100 text-red-600': n.type === 'error',
                                            'bg-amber-100 text-amber-600': n.type === 'warning',
                                            'bg-blue-100 text-blue-600': n.type !== 'success' && n.type !== 'error' && n.type !== 'warning',
                                        }" class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm">
                                            <span x-text="n.type === 'success' ? '✓' : (n.type === 'error' ? '✕' : (n.type === 'warning' ? '⚠' : 'ℹ'))"></span>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate" x-text="n.title"></p>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="n.message"></p>
                                        <p class="text-[10px] text-gray-400 mt-1" x-text="n.time"></p>
                                    </div>
                                    <div class="flex-shrink-0 flex items-center gap-1">
                                        <a x-show="n.link" :href="n.link"
                                           class="text-indigo-500 hover:text-indigo-700 text-xs font-medium">
                                            {{ __('View') }}
                                        </a>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            @endauth

        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto p-4 sm:p-6">
            {{ $slot }}
        </main>

    </div>

</div>

{{-- Global notification polling + toast system --}}
<script>
    // Toast helper — available globally
    window.__showToast = function(type, title, message) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const colorMap = {
            success: { bg: '#16a34a', text: '#ffffff', icon: '✓' },
            error:   { bg: '#dc2626', text: '#ffffff', icon: '✕' },
            warning: { bg: '#f59e0b', text: '#ffffff', icon: '⚠' },
            info:    { bg: '#4f46e5', text: '#ffffff', icon: 'ℹ' },
        };
        const c = colorMap[type] || colorMap.info;

        const el = document.createElement('div');
        el.className = 'toast-item';
        el.style.cssText = `background:${c.bg}; color:${c.text}; width:320px; border-radius:12px; padding:14px 16px; display:flex; align-items:flex-start; gap:10px; box-shadow:0 8px 24px rgba(0,0,0,.18); transition:all .3s ease; opacity:1; transform:translateY(0);`;
        el.innerHTML = `
            <span style="font-size:18px; flex-shrink:0; margin-top:1px;">${c.icon}</span>
            <div style="flex:1; min-width:0;">
                <p style="font-size:14px; font-weight:600; margin:0;">${title}</p>
                <p style="font-size:12px; margin:4px 0 0; opacity:.85; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${message}</p>
            </div>
            <button style="background:none; border:none; color:rgba(255,255,255,.7); cursor:pointer; font-size:14px; flex-shrink:0; padding:0;" onclick="this.closest('.toast-item').remove()">✕</button>
        `;

        container.appendChild(el);

        // Auto-dismiss after 4s
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-1rem)';
            setTimeout(() => el.remove(), 300);
        }, 4000);
    };

    // Alpine component for notification bell
    window.notificationBell = function(initialCount) {
        return {
            open: false,
            unreadCount: initialCount || 0,
            items: [],
            lastId: 0,
            init() {
                // Fetch initial list
                this.poll(false);
                // Poll every 10 seconds
                setInterval(() => this.poll(true), 5000);
            },
            async poll(showToasts) {
                try {
                    const resp = await fetch(`/notifications/poll?last_id=${this.lastId}`);
                    if (!resp.ok) return;
                    const data = await resp.json();

                    this.unreadCount = data.unread_count;

                    if (data.notifications.length > 0) {
                        // Show toast for each new notification (only after initial load)
                        if (showToasts && this.lastId > 0) {
                            data.notifications.forEach(n => {
                                if (n.id > this.lastId) {
                                    window.__showToast(n.type, n.title, n.message);
                                }
                            });
                        }

                        // Update items (latest 10 for dropdown)
                        this.items = data.notifications;

                        // Track highest ID
                        const maxId = Math.max(...data.notifications.map(n => n.id));
                        if (maxId > this.lastId) {
                            this.lastId = maxId;
                        }
                    }
                } catch (e) {
                    // Silently fail on network errors
                }
            }
        };
    };
</script>

</body>
</html>

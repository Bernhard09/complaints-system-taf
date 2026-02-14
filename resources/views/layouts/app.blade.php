<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div
            x-data="{ open: false }"
            class="relative min-h-screen"
        >
                {{-- OVERLAY --}}
                <div
                    x-show="open"
                    x-transition.opacity
                    class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
                    @click="open = false"
                ></div>
                {{-- DRAWER --}}
                @auth
                <aside
                    x-show="open"
                    x-transition:enter="transition transform duration-300"
                    x-transition:enter-start="-translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition transform duration-300"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="-translate-x-full"
                    class="fixed top-0 left-0 h-full w-72 bg-white shadow-2xl z-50"
                >
                    @include('layouts.sidebar')
                </aside>
                @endauth
                @isset($header)
                <header class="bg-white border-b px-6 py-4 flex items-center justify-between">

                    <div class="flex items-center gap-4">
                        <button
                            @click="open = true"
                            class="p-2 rounded-lg hover:bg-gray-100"
                        >
                            ☰
                        </button>

                        <div>
                            {{ $header }}
                        </div>
                    </div>

                    <div class="text-sm text-gray-600">
                        {{ Auth::user()->name ?? '' }}
                    </div>

                </header>
                @endisset
                    {{-- MAIN CONTENT --}}
                <div class="relative z-10 flex flex-col min-h-screen">
                    {{ $slot }}
                </div>
        </div>

    </body>
</html>

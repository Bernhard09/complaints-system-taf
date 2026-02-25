<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>

            <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="h-screen overflow-hidden bg-gray-100">

<div x-data="{ collapsed: false, mobileOpen: false }"
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
    <div class="flex-1 flex flex-col">

        <header class="bg-white border-b px-6 py-4 flex items-center justify-between">

            <div class="flex items-center gap-4">

                <button class="md:hidden"
                        @click="mobileOpen = true">
                    ☰
                </button>

                <button class="hidden md:block"
                        @click="collapsed = !collapsed">
                    ⇔
                </button>

                <h1 class="text-lg font-semibold text-gray-800">
                    {{ $pageTitle ?? '' }}
                </h1>

            </div>

        </header>

        <main class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </main>

    </div>

</div>

</body>
</html>

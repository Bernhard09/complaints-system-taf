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
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex text-gray-900">
            
            {{-- Left Side: Branding / Visual (Hidden on mobile) --}}
            <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-700 via-indigo-800 to-indigo-900 items-center justify-center relative overflow-hidden">
                {{-- Decorative circles --}}
                <div class="absolute top-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full mix-blend-overlay filter blur-3xl transform -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-96 h-96 bg-indigo-500 opacity-20 rounded-full mix-blend-overlay filter blur-3xl transform translate-x-1/3 translate-y-1/3"></div>
                
                <div class="relative z-10 text-center px-12">
                    <x-application-logo class="w-24 h-24 text-white mx-auto mb-8 drop-shadow-md" />
                    <h1 class="text-4xl font-bold text-white tracking-tight mb-4">Complaint System</h1>
                    <p class="text-indigo-200 text-lg max-w-md mx-auto">
                        Your voice matters. Report issues efficiently and track resolutions in real-time.
                    </p>
                </div>
            </div>

            {{-- Right Side: Form Container --}}
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center bg-gray-50 px-6 py-12 lg:px-16 relative">
                
                {{-- Mobile Logo (Only visible on small screens) --}}
                <div class="lg:hidden mb-10 text-center">
                    <a href="/">
                        <x-application-logo class="w-16 h-16 text-indigo-600 mx-auto" />
                    </a>
                    <h2 class="mt-4 text-2xl font-bold text-gray-900">Complaint System</h2>
                </div>

                {{-- The Form Card --}}
                <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden p-8 border border-gray-100">
                    {{ $slot }}
                </div>
                
                {{-- Simple Footer --}}
                <div class="mt-8 text-center text-sm text-gray-500">
                    &copy; {{ date('Y') }} Complaint System. All rights reserved.
                </div>
            </div>
        </div>
    </body>
</html>

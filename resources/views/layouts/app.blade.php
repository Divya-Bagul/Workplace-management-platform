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
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: false }">
            <div
                x-show="sidebarOpen"
                x-transition.opacity
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"
                aria-hidden="true"
            ></div>

            <div class="flex min-h-screen">
                @include('layouts.sidebar')

                <div class="flex min-w-0 flex-1 flex-col">
                    @include('layouts.navigation')

                    @if (isset($header))
                        <header class="border-b border-gray-200 bg-white shadow-sm">
                            <div class="px-4 py-6 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    <main class="flex-1">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>

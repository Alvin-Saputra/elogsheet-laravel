<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} - @yield('page_title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Load Tailwind & Alpine --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/focus@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Flowbite -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

    {{-- Custom Alpine Store --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('sidebar', {
                open: window.innerWidth >= 768,
                toggle() {
                    this.open = !this.open;
                }
            });

            // Auto-close on mobile when resizing
            window.addEventListener('resize', () => {
                Alpine.store('sidebar').open = window.innerWidth >= 768;
            });
        });
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 h-screen ">
    <div class="flex h-full" x-data x-cloak>
        {{-- Sidebar --}}
        @include('layouts.sidebar')

        {{-- Overlay for mobile --}}
        <div x-show="$store.sidebar.open" x-transition.opacity
            class="fixed inset-0 bg-black bg-opacity-25 z-20 md:hidden" @click="$store.sidebar.toggle()" x-cloak>
        </div>

        {{-- Content --}}
        <div class="flex flex-col flex-1 overflow-hidden w-full transition-all duration-300 ease-in-out"
            :class="{ 'md:ml-0': !$store.sidebar.open, 'md:ml-64': $store.sidebar.open }">
            {{-- Navbar --}}
            @include('layouts.navbar')

            {{-- Main Content --}}
            <main
                class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200 p-4 md:p-6">
                @yield('content')
            </main>

            {{-- Footer --}}
            <footer class="bg-white border-t p-4 text-sm text-gray-500 text-center shadow">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </footer>
        </div>
    </div>
    {{-- Page specific scripts --}}
    @stack('scripts')

</body>

</html>
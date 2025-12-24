<aside class="z-30 w-64 bg-white border-r shadow-md h-screen fixed transition-transform duration-300 ease-in-out"
    :class="{
           '-translate-x-full': !$store.sidebar.open, 
           'translate-x-0': $store.sidebar.open
       }" x-cloak>
    <div class="h-full flex flex-col">
        <!-- Header -->
        <div class="p-5 border-b flex justify-between items-center">
            <h1 class="text-lg font-bold text-gray-800">E-{{ config('app.name') }} v.1.0.13</h1>
            <button @click="$store.sidebar.toggle()" class="md:hidden text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Menu -->
        @php
            $menus = session('menus', collect());
        @endphp

        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2 text-sm text-gray-700">
            @foreach ($menus as $menu)
                @php
                    $childRoutes = $menu->children->pluck('route_name')->toArray();
                    $isActiveParent = collect($childRoutes)->some(fn($r) => request()->routeIs($r));
                @endphp

                @if ($menu->children->count())
                    {{-- Parent menu dengan submenu --}}
                    <div x-data="{ open: @json($isActiveParent) }">
                        <button @click="open = !open"
                            class="flex items-center justify-between w-full px-3 py-2 rounded hover:bg-gray-100">
                            <div class="flex items-center gap-3">
                                <i class="{{ $menu->icon ?? 'fa fa-folder' }}"></i>
                                {{ $menu->menu_name }}
                            </div>
                            <svg :class="{ 'rotate-90': open }" class="w-4 h-4 text-gray-500 transition-transform duration-200"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse x-cloak class="mt-1 ml-6 space-y-1 border-l pl-4">
                            @foreach ($menu->children as $child)
                                @php $isActiveChild = request()->routeIs($child->route_name); @endphp
                                <a href="{{ $child->route_name && Route::has($child->route_name) ? route($child->route_name) : '#' }}"
                                    class="block px-2 py-1 rounded hover:bg-gray-100 {{ $isActiveChild ? 'bg-gray-200 font-semibold' : '' }}">
                                    • {{ $child->menu_name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Menu tanpa submenu --}}
                    @php $isActive = request()->routeIs($menu->route_name); @endphp
                    <a href="{{ $menu->route_name && Route::has($menu->route_name) ? route($menu->route_name) : '#' }}"
                        class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 {{ $isActive ? 'bg-gray-200 font-semibold' : '' }}">
                        <i class="{{ $menu->icon ?? 'fa fa-circle' }}"></i>
                        {{ $menu->menu_name }}
                    </a>
                @endif
            @endforeach
        </nav>

        <!-- Sidebar Footer -->
        <div class="px-4 py-4 mt-auto">
            <div class="bg-white rounded-lg p-3 flex flex-col space-y-2">
                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 flex items-center justify-center bg-blue-100 text-blue-700 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </div>
                    <span class="text-gray-700 text-sm font-medium">{{ session('business_unit_name', '-') }}</span>
                </div>

                <div class="flex items-center gap-2">
                    <div class="w-5 h-5 flex items-center justify-center bg-green-100 text-green-700 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-gray-700 text-sm font-medium">{{ session('plant_code', '-') }}</span>
                </div>
            </div>
        </div>
    </div>
</aside>
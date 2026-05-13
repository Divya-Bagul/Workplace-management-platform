@php
    $canHr = auth()->user()->hasAnyRole(['admin', 'hr']);
    $canIt = auth()->user()->hasAnyRole(['admin', 'it']);
    $canWorkplace = auth()->user()->hasAnyRole(['admin', 'hr', 'it']);
    $isAdmin = auth()->user()->hasRole('admin');
@endphp

<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-gray-200 bg-white transition-transform duration-200 ease-in-out lg:static lg:z-auto lg:translate-x-0"
>
    <div class="flex h-full flex-col">
        <div class="flex items-center gap-3 border-b border-gray-100 px-4 py-4 lg:hidden">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <x-application-logo class="block h-8 w-auto fill-current text-gray-800" />
                <span class="text-sm font-semibold text-gray-900">{{ config('app.name', 'zylitix') }}</span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <div class="space-y-1">
                <x-sidebar-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-sidebar-nav-link>

                @if ($canWorkplace)
                    <x-sidebar-nav-link :href="route('reports.dashboard')" :active="request()->routeIs('reports.*')">
                        {{ __('Reports') }}
                    </x-sidebar-nav-link>
                @endif
            </div>

            @if ($canHr)
                <div class="mt-6">
                    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ __('HR Management Module') }}
                    </p>
                    <div class="space-y-1">
                        <x-sidebar-nav-link :href="route('employees.index')" :active="request()->routeIs('employees.*')">
                            {{ __('Employees') }}
                        </x-sidebar-nav-link>
                        <x-sidebar-nav-link :href="route('onboarding.index')" :active="request()->routeIs('onboarding.*')">
                            {{ __('Onboarding') }}
                        </x-sidebar-nav-link>
                        <x-sidebar-nav-link :href="route('offboarding.index')" :active="request()->routeIs('offboarding.*')">
                            {{ __('Offboarding') }}
                        </x-sidebar-nav-link>
                    </div>
                </div>
            @elseif ($canWorkplace)
                <div class="mt-6">
                    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ __('HR Management Module') }}
                    </p>
                    <div class="space-y-1">
                        <x-sidebar-nav-link :href="route('onboarding.index')" :active="request()->routeIs('onboarding.*')">
                            {{ __('Onboarding') }}
                        </x-sidebar-nav-link>
                    </div>
                </div>
            @endif

            @if ($canIt)
                <div class="mt-6">
                    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ __('IT Asset Management Module') }}
                    </p>
                    <div class="space-y-1">
                        <x-sidebar-nav-link :href="route('assets.index')" :active="request()->routeIs('assets.*')">
                            {{ __('IT assets') }}
                        </x-sidebar-nav-link>
                    </div>
                </div>
            @endif

            @if ($canWorkplace)
                <div class="mt-6">
                    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ __('Desk Allocation System') }}
                    </p>
                    <div class="space-y-1">
                        <x-sidebar-nav-link :href="route('desks.index')" :active="request()->routeIs('desks.*')">
                            {{ __('Desks') }}
                        </x-sidebar-nav-link>
                    </div>
                </div>
            @endif

            @if ($isAdmin)
                <div class="mt-6">
                    <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                        {{ __('Administration') }}
                    </p>
                    <div class="space-y-1">
                        <x-sidebar-nav-link
                            :href="route('admin.buildings.index')"
                            :active="request()->routeIs('admin.buildings.*') || request()->routeIs('admin.departments.*')"
                        >
                            {{ __('Buildings & departments') }}
                        </x-sidebar-nav-link>
                        <x-sidebar-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                            {{ __('Audit log') }}
                        </x-sidebar-nav-link>
                    </div>
                </div>
            @endif
        </nav>
    </div>
</aside>

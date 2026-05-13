<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-600">
                    {{ __('Operational snapshots across HR, IT assets, and desk allocation. Counts refresh from live workplace data.') }}
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Active employees') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['employees_active'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Total records: :count', ['count' => $summary['employees_total']]) }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Open onboarding') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['onboarding_open'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Open offboarding') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['offboarding_open'] }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <p class="text-sm font-medium text-gray-500">{{ __('Desks available / occupied') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $summary['desks_available'] }} / {{ $summary['desks_occupied'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Total desks: :count', ['count' => $summary['desks_total']]) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('HR Management Module') }}</h3>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Onboarding and offboarding status mix') }}</p>

                    <div class="mt-4 space-y-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Onboarding') }}</p>
                            <dl class="mt-2 divide-y divide-gray-100">
                                @forelse ($onboardingByStatus as $status => $total)
                                    <div class="flex items-center justify-between py-2 text-sm">
                                        <dt class="text-gray-600">{{ str_replace('_', ' ', $status) }}</dt>
                                        <dd class="font-semibold text-gray-900">{{ $total }}</dd>
                                    </div>
                                @empty
                                    <p class="py-2 text-sm text-gray-500">{{ __('No onboarding records yet.') }}</p>
                                @endforelse
                            </dl>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Offboarding') }}</p>
                            <dl class="mt-2 divide-y divide-gray-100">
                                @forelse ($offboardingByStatus as $status => $total)
                                    <div class="flex items-center justify-between py-2 text-sm">
                                        <dt class="text-gray-600">{{ str_replace('_', ' ', $status) }}</dt>
                                        <dd class="font-semibold text-gray-900">{{ $total }}</dd>
                                    </div>
                                @empty
                                    <p class="py-2 text-sm text-gray-500">{{ __('No offboarding records yet.') }}</p>
                                @endforelse
                            </dl>
                        </div>

                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-400">{{ __('Employees') }}</p>
                            <dl class="mt-2 divide-y divide-gray-100">
                                @forelse ($employeesByStatus as $status => $total)
                                    <div class="flex items-center justify-between py-2 text-sm">
                                        <dt class="text-gray-600">{{ str_replace('_', ' ', $status) }}</dt>
                                        <dd class="font-semibold text-gray-900">{{ $total }}</dd>
                                    </div>
                                @empty
                                    <p class="py-2 text-sm text-gray-500">{{ __('No employees yet.') }}</p>
                                @endforelse
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('IT Asset Management Module') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Inventory and assignment status') }}</p>
                        <dl class="mt-4 divide-y divide-gray-100">
                            @forelse ($assetsByStatus as $status => $total)
                                <div class="flex items-center justify-between py-2 text-sm">
                                    <dt class="text-gray-600">{{ str_replace('_', ' ', $status) }}</dt>
                                    <dd class="font-semibold text-gray-900">{{ $total }}</dd>
                                </div>
                            @empty
                                <p class="py-2 text-sm text-gray-500">{{ __('No assets yet.') }}</p>
                            @endforelse
                        </dl>
                        <p class="mt-4 text-xs text-gray-500">
                            {{ __('Assigned: :assigned · In stock: :stock · Total: :total', [
                                'assigned' => $summary['assets_assigned'],
                                'stock' => $summary['assets_in_stock'],
                                'total' => $summary['assets_total'],
                            ]) }}
                        </p>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Desk Allocation System') }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Desk availability across the workplace') }}</p>
                        <dl class="mt-4 divide-y divide-gray-100">
                            @forelse ($desksByStatus as $status => $total)
                                <div class="flex items-center justify-between py-2 text-sm">
                                    <dt class="text-gray-600">{{ str_replace('_', ' ', $status) }}</dt>
                                    <dd class="font-semibold text-gray-900">{{ $total }}</dd>
                                </div>
                            @empty
                                <p class="py-2 text-sm text-gray-500">{{ __('No desks yet.') }}</p>
                            @endforelse
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

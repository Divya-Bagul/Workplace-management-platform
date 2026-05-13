<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workplace overview') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @unless(auth()->user()->hasAnyRole(['admin', 'hr', 'it']))
                <div class="bg-amber-50 border border-amber-200 text-amber-900 px-4 py-3 rounded-lg shadow-sm">
                    {{ __('Your account does not have a workplace role yet. Ask an administrator to assign Admin, HR, or IT access.') }}
                </div>
            @endunless

            @hasanyrole('admin|hr|it')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-3">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Where to go next') }}</h3>
                    @hasanyrole('admin|hr')
                        <p class="text-sm text-gray-600">{{ __('Hire flow: Employees → Onboarding → assign desk → forward to IT → wait for IT setup and assets → complete onboarding.') }}</p>
                        <div class="flex flex-wrap gap-2 text-sm">
                            <a href="{{ route('employees.index') }}" class="text-indigo-700 hover:text-indigo-900">{{ __('Employees') }}</a>
                            <span class="text-gray-400">→</span>
                            <a href="{{ route('onboarding.index') }}" class="text-indigo-700 hover:text-indigo-900">{{ __('Onboarding') }}</a>
                            <span class="text-gray-400">→</span>
                            <span class="text-gray-600">{{ __('IT setup') }}</span>
                            <span class="text-gray-400">→</span>
                            <span class="text-gray-600">{{ __('Complete onboarding') }}</span>
                        </div>
                    @endhasanyrole
                    @hasanyrole('it')
                        <p class="text-sm text-gray-600">{{ __('IT flow: open Onboarding queue → start setup → assign assets in IT assets → mark setup complete → HR closes onboarding.') }}</p>
                        <div class="flex flex-wrap gap-2 text-sm">
                            <a href="{{ route('onboarding.index') }}" class="text-indigo-700 hover:text-indigo-900">{{ __('Onboarding') }}</a>
                            <span class="text-gray-400">→</span>
                            <a href="{{ route('assets.index') }}" class="text-indigo-700 hover:text-indigo-900">{{ __('IT assets') }}</a>
                        </div>
                    @endhasanyrole
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Active employees') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['employees'] }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Desks available / occupied') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['desks_available'] }} / {{ $stats['desks_occupied'] }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Assets in stock') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['assets_in_stock'] }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Open onboarding') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['onboarding_open'] }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('Open offboarding') }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['offboarding_open'] }}</p>
                    </div>
                </div>
            @endhasanyrole
        </div>
    </div>
</x-app-layout>

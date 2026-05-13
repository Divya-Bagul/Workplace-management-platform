<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Offboarding') }} · {{ $offboarding->employee?->name }}</h2>
            <a href="{{ route('offboarding.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Back') }}</a>
        </div>
    </x-slot>

    <div class="py-10 space-y-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-900 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">{{ __('Status') }}</p>
                    <p class="text-lg font-semibold">{{ $offboarding->status }}</p>
                </div>
                <div>
                    <p class="text-gray-500">{{ __('Last working day') }}</p>
                    <p class="text-lg font-semibold">{{ $offboarding->last_working_day?->format('Y-m-d') }}</p>
                </div>
            </div>

            @hasanyrole('admin|it')
                @if (! in_array($offboarding->status, ['completed', 'cancelled'], true))
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-3">
                        <h3 class="font-medium text-gray-900">{{ __('IT asset recovery') }}</h3>
                        @if ($offboarding->status === 'pending')
                            <form method="post" action="{{ route('offboarding.recovery.start', $offboarding) }}">@csrf
                                <x-primary-button type="submit">{{ __('Start recovery workflow') }}</x-primary-button>
                            </form>
                        @endif
                        @if (in_array($offboarding->status, ['pending', 'recovery_in_progress'], true))
                            <form method="post" action="{{ route('offboarding.recovery.assets', $offboarding) }}">@csrf
                                <x-primary-button type="submit">{{ __('Mark assets recovered / returned') }}</x-primary-button>
                            </form>
                        @endif
                    </div>
                @endif
            @endhasanyrole

            @hasanyrole('admin|hr|it')
                @if ($offboarding->status === 'assets_recovered')
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="post" action="{{ route('offboarding.release-desk', $offboarding) }}">@csrf
                            <x-primary-button type="submit">{{ __('Release desk after clearance') }}</x-primary-button>
                        </form>
                    </div>
                @endif
            @endhasanyrole

            @hasanyrole('admin|hr')
                @if ($offboarding->status === 'desk_released')
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="post" action="{{ route('offboarding.complete', $offboarding) }}">@csrf
                            <x-primary-button type="submit">{{ __('Complete offboarding') }}</x-primary-button>
                        </form>
                    </div>
                @endif
                @if (! in_array($offboarding->status, ['completed', 'cancelled'], true))
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="post" action="{{ route('offboarding.cancel', $offboarding) }}" onsubmit="return confirm('{{ __('Cancel offboarding?') }}');">@csrf
                            <x-secondary-button type="submit">{{ __('Cancel request') }}</x-secondary-button>
                        </form>
                    </div>
                @endif
            @endhasanyrole

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-600 space-y-1">
                <p>{{ __('Recovery started') }}: {{ $offboarding->assets_recovery_started_at?->format('Y-m-d H:i') ?? '—' }}</p>
                <p>{{ __('Assets recovered') }}: {{ $offboarding->assets_recovered_at?->format('Y-m-d H:i') ?? '—' }}</p>
                <p>{{ __('Desk released') }}: {{ $offboarding->desk_released_at?->format('Y-m-d H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>
</x-app-layout>

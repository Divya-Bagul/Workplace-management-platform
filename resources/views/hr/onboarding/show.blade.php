<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Onboarding') }} · {{ $onboarding->employee?->name }}</h2>
            <a href="{{ route('onboarding.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Back to list') }}</a>
        </div>
    </x-slot>

    <div class="py-10 space-y-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-900 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            <x-onboarding-workflow-guide :onboarding="$onboarding" />

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <p class="text-gray-500">{{ __('Status') }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $onboarding->status }}</p>
                </div>
                <div>
                    <p class="text-gray-500">{{ __('Employee') }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $onboarding->employee?->name }} ({{ $onboarding->employee?->employee_code }})</p>
                    <p class="text-gray-600">{{ __('Joining') }}: {{ $onboarding->employee?->joining_date?->format('Y-m-d') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">{{ __('Department / designation') }}</p>
                    <p class="text-gray-900">{{ $onboarding->employee?->department?->name ?? '—' }} · {{ $onboarding->employee?->designation ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">{{ __('Office location') }}</p>
                    <p class="text-gray-900">{{ $onboarding->employee?->building?->name ?? '—' }} @if($onboarding->employee && $onboarding->employee->floor) · {{ $onboarding->employee->floor->name }} @endif</p>
                </div>
                <div>
                    <p class="text-gray-500">{{ __('Asset providing') }}</p>
                    <p class="text-lg font-semibold text-gray-900">
                        @if ($onboarding->tracksAssetProvision())
                            {{ __($onboarding->assetProvisionStatus()) }}
                        @else
                            —
                        @endif
                    </p>
                    @if ($onboarding->tracksAssetProvision() && $onboarding->assetProvisionStatus() === 'provided')
                        <ul class="mt-2 text-gray-600 space-y-1">
                            @foreach ($onboarding->activeEmployeeAssetAssignments() as $assignment)
                                <li>{{ $assignment->asset?->asset_tag ?? '—' }} · {{ $assignment->asset?->assetType?->name ?? '—' }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="md:col-span-2">
                    <p class="text-gray-500">{{ __('Desk') }}</p>
                    <p class="text-gray-900">
                        @if ($onboarding->desk)
                            {{ $onboarding->desk->code }} — {{ $onboarding->desk->floor?->building?->name }} / {{ $onboarding->desk->floor?->name }}
                        @else
                            {{ __('Not assigned') }}
                        @endif
                    </p>
                </div>
            </div>

            @hasanyrole('admin|hr')
                @if ($onboarding->allowsHrDeskSetup())
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <h3 class="font-medium text-gray-900">{{ __('Assign / change desk') }}</h3>
                        <form method="post" action="{{ route('onboarding.assign-desk', $onboarding) }}" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                            @csrf
                            <div class="flex-1">
                                <x-input-label for="desk_id_assign" :value="__('Desk')" />
                                <select id="desk_id_assign" name="desk_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                    @foreach ($desks as $d)
                                        <option value="{{ $d->id }}" @selected($onboarding->desk_id === $d->id)>
                                            {{ $d->code }} · {{ $d->floor?->building?->name }} / {{ $d->floor?->name }} ({{ $d->status }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('desk_id')" />
                            </div>
                            <x-primary-button type="submit">{{ __('Save desk') }}</x-primary-button>
                        </form>

                        <form method="post" action="{{ route('onboarding.forward-it', $onboarding) }}" class="space-y-3 border-t border-gray-100 pt-4">
                            @csrf
                            <div>
                                <x-input-label for="it_notes" :value="__('Notes for IT (optional)')" />
                                <textarea id="it_notes" name="it_notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('it_notes', $onboarding->it_notes) }}</textarea>
                            </div>
                            <x-primary-button type="submit">{{ __('Forward to IT for system setup') }}</x-primary-button>
                            <x-input-error class="mt-2" :messages="$errors->get('desk')" />
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </form>

                        <form method="post" action="{{ route('onboarding.cancel', $onboarding) }}" class="border-t border-gray-100 pt-4" onsubmit="return confirm('{{ __('Cancel this onboarding?') }}');">
                            @csrf
                            <x-secondary-button type="submit">{{ __('Cancel onboarding') }}</x-secondary-button>
                        </form>
                    </div>
                @elseif ($onboarding->allowsHrCompletion())
                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <form method="post" action="{{ route('onboarding.complete', $onboarding) }}">
                            @csrf
                            <x-primary-button type="submit">{{ __('Mark onboarding completed') }}</x-primary-button>
                        </form>
                    </div>
                @endif
            @endhasanyrole

            @hasanyrole('it')
                @if (in_array($onboarding->status, ['it_pending', 'it_in_progress'], true))
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                        <h3 class="font-medium text-gray-900">{{ __('IT system setup') }}</h3>
                        @if ($onboarding->tracksAssetProvision())
                            <p class="text-sm text-gray-600">
                                {{ __('Asset providing') }}:
                                <span class="font-medium text-gray-900">{{ __($onboarding->assetProvisionStatus()) }}</span>
                            </p>
                            @if ($onboarding->assetProvisionStatus() === 'pending')
                                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-100 rounded-md px-3 py-2">
                                    {{ __('Assign IT assets to this employee from the IT assets page before completing setup.') }}
                                    <a href="{{ route('assets.index') }}" class="font-medium text-indigo-700 hover:text-indigo-900">{{ __('Open IT assets') }}</a>
                                </p>
                            @endif
                        @endif
                        @if ($onboarding->status === 'it_pending')
                            <form method="post" action="{{ route('onboarding.it.start', $onboarding) }}">
                                @csrf
                                <x-primary-button type="submit">{{ __('Start setup') }}</x-primary-button>
                            </form>
                        @endif
                        @if (in_array($onboarding->status, ['it_pending', 'it_in_progress'], true))
                            <form method="post" action="{{ route('onboarding.it.complete', $onboarding) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <x-input-label for="it_notes_complete" :value="__('IT notes')" />
                                    <textarea id="it_notes_complete" name="it_notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('it_notes', $onboarding->it_notes) }}</textarea>
                                </div>
                                <x-primary-button type="submit">{{ __('Mark system setup complete') }}</x-primary-button>
                            </form>
                        @endif
                    </div>
                @endif
            @endhasanyrole

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-600 space-y-1">
                <p>{{ __('Submitted to IT') }}: {{ $onboarding->submitted_to_it_at?->format('Y-m-d H:i') ?? '—' }}</p>
                <p>{{ __('IT started') }}: {{ $onboarding->it_setup_started_at?->format('Y-m-d H:i') ?? '—' }}</p>
                <p>{{ __('IT completed') }}: {{ $onboarding->it_setup_completed_at?->format('Y-m-d H:i') ?? '—' }}</p>
                <p>{{ __('Desk assigned at') }}: {{ $onboarding->desk_assigned_at?->format('Y-m-d H:i') ?? '—' }}</p>
            </div>
        </div>
    </div>

    @if (session('focus_onboarding_flow'))
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const target = document.getElementById('onboarding-flow');
                    if (!target) {
                        return;
                    }

                    if (window.location.hash !== '#onboarding-flow') {
                        window.location.hash = 'onboarding-flow';
                    }

                    window.requestAnimationFrame(function () {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                });
            </script>
        @endpush
    @endif
</x-app-layout>

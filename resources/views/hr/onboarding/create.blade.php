<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New onboarding') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <p class="text-sm text-gray-600">{{ __('Create a request, optionally assign a desk using the employee joining date, then forward to IT for system setup.') }}</p>
                <form method="post" action="{{ route('onboarding.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="employee_id" :value="__('Employee')" />
                        <select id="employee_id" name="employee_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                            <option value="">{{ __('— Select —') }}</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}" @selected(old('employee_id') == $e->id)>{{ $e->name }} ({{ $e->employee_code }})</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
                    </div>
                    <div>
                        <x-input-label for="desk_id" :value="__('Desk (optional)')" />
                        <select id="desk_id" name="desk_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('— Assign later —') }}</option>
                            @foreach ($desks as $d)
                                <option value="{{ $d->id }}" @selected(old('desk_id') == $d->id)>
                                    {{ $d->code }} · {{ $d->floor?->building?->name }} / {{ $d->floor?->name }} ({{ $d->status }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('desk_id')" />
                    </div>
                    <div>
                        <x-input-label for="hr_notes" :value="__('HR notes')" />
                        <textarea id="hr_notes" name="hr_notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('hr_notes') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('hr_notes')" />
                    </div>
                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Create onboarding') }}</x-primary-button>
                        <a href="{{ route('onboarding.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

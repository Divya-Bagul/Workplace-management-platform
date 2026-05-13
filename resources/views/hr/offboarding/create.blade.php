<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New offboarding') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="post" action="{{ route('offboarding.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="employee_id" :value="__('Employee')" />
                        <select id="employee_id" name="employee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">{{ __('— Select —') }}</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}" @selected(old('employee_id') == $e->id)>{{ $e->name }} ({{ $e->employee_code }})</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
                    </div>
                    <div>
                        <x-input-label for="last_working_day" :value="__('Last working day')" />
                        <x-text-input id="last_working_day" name="last_working_day" type="date" class="mt-1 block w-full" :value="old('last_working_day')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('last_working_day')" />
                    </div>
                    <div>
                        <x-input-label for="hr_notes" :value="__('HR notes')" />
                        <textarea id="hr_notes" name="hr_notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('hr_notes') }}</textarea>
                    </div>
                    <div class="flex items-center gap-4">
                        <x-primary-button type="submit">{{ __('Start offboarding') }}</x-primary-button>
                        <a href="{{ route('offboarding.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

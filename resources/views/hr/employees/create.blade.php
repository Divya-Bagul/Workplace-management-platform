<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add employee') }}</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
                <form method="post" action="{{ route('employees.store') }}" class="space-y-6">
                    @csrf
                    <div>
                        <x-input-label for="employee_code" :value="__('Employee ID / Code')" />
                        <x-text-input id="employee_code" name="employee_code" type="text" class="mt-1 block w-full" :value="old('employee_code')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('employee_code')" />
                    </div>
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>
                        <div>
                            <x-input-label for="phone" :value="__('Phone')" />
                            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone')" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="department_id" :value="__('Department')" />
                        <select id="department_id" name="department_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('— Select —') }}</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('department_id')" />
                    </div>
                    <div>
                        <x-input-label for="designation" :value="__('Designation')" />
                        <x-text-input id="designation" name="designation" type="text" class="mt-1 block w-full" :value="old('designation')" />
                        <x-input-error class="mt-2" :messages="$errors->get('designation')" />
                    </div>
                    <div>
                        <x-input-label for="joining_date" :value="__('Joining date')" />
                        <x-text-input id="joining_date" name="joining_date" type="date" class="mt-1 block w-full" :value="old('joining_date')" />
                        <x-input-error class="mt-2" :messages="$errors->get('joining_date')" />
                    </div>
                    <div>
                        <x-input-label for="reporting_manager_id" :value="__('Reporting manager')" />
                        <select id="reporting_manager_id" name="reporting_manager_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">{{ __('— None —') }}</option>
                            @foreach ($managers as $m)
                                <option value="{{ $m->id }}" @selected(old('reporting_manager_id') == $m->id)>{{ $m->name }} ({{ $m->employee_code }})</option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('reporting_manager_id')" />
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="building_id" :value="__('Office building')" />
                            <select id="building_id" name="building_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('— Select —') }}</option>
                                @foreach ($buildings as $b)
                                    <option value="{{ $b->id }}" @selected(old('building_id') == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('building_id')" />
                        </div>
                        <div>
                            <x-input-label for="floor_id" :value="__('Office floor')" />
                            <select id="floor_id" name="floor_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">{{ __('— Select —') }}</option>
                                @foreach ($floors as $f)
                                    <option value="{{ $f->id }}" @selected(old('floor_id') == $f->id)>{{ $f->building?->name }} · {{ $f->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('floor_id')" />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Save') }}</x-primary-button>
                        <a href="{{ route('employees.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

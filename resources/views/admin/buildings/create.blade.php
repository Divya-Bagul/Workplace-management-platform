<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add building') }}</h2></x-slot>
    <div class="py-10 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="post" action="{{ route('admin.buildings.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="address" :value="__('Address')" />
                    <x-text-input id="address" name="address" class="mt-1 block w-full" :value="old('address')" />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>
                <div class="flex gap-3">
                    <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('admin.buildings.index') }}" class="text-sm text-gray-600 self-center">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

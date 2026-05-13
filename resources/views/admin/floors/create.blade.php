<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add floor') }} — {{ $building->name }}</h2></x-slot>
    <div class="py-10 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="post" action="{{ route('admin.buildings.floors.store', $building) }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="name" :value="__('Floor name')" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="level" :value="__('Level number')" />
                    <x-text-input id="level" name="level" type="number" class="mt-1 block w-full" :value="old('level', 0)" />
                    <x-input-error :messages="$errors->get('level')" class="mt-2" />
                </div>
                <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>

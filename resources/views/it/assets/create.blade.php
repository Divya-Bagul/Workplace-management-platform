<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Register asset') }}</h2>
            <a href="{{ route('assets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Back') }}</a>
        </div>
    </x-slot>
    <div class="py-10 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="post" action="{{ route('assets.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="asset_type_id" :value="__('Type')" />
                    <select id="asset_type_id" name="asset_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @foreach ($assetTypes as $t)
                            <option value="{{ $t->id }}" @selected(old('asset_type_id') == $t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('asset_type_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="asset_tag" :value="__('Asset tag')" />
                    <x-text-input id="asset_tag" name="asset_tag" class="mt-1 block w-full" :value="old('asset_tag')" required />
                    <x-input-error :messages="$errors->get('asset_tag')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="serial_number" :value="__('Serial number')" />
                    <x-text-input id="serial_number" name="serial_number" class="mt-1 block w-full" :value="old('serial_number')" />
                </div>
                <div>
                    <x-input-label for="notes" :value="__('Notes')" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                </div>
                <x-primary-button type="submit">{{ __('Save') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>

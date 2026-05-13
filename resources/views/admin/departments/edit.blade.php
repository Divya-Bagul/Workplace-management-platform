<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit department') }}</h2></x-slot>
    <div class="py-10 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <form method="post" action="{{ route('admin.departments.update', $department) }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $department->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="code" :value="__('Code')" />
                    <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $department->code)" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>

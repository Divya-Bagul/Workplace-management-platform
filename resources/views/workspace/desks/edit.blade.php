<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit desk') }} {{ $desk->code }}</h2></x-slot>
    <div class="py-10 max-w-xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6">
            <form method="post" action="{{ route('desks.update', $desk) }}" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <x-input-label for="floor_id" :value="__('Floor')" />
                    <select id="floor_id" name="floor_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @foreach ($floors as $f)
                            <option value="{{ $f->id }}" @selected(old('floor_id', $desk->floor_id) == $f->id)>{{ $f->building?->name }} · {{ $f->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('floor_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="code" :value="__('Desk code')" />
                    <x-text-input id="code" name="code" class="mt-1 block w-full" :value="old('code', $desk->code)" required />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        @foreach (['available', 'occupied', 'reserved'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $desk->status) === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="notes" :value="__('Notes')" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('notes', $desk->notes) }}</textarea>
                </div>
                <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
            </form>
            <form method="post" action="{{ route('desks.destroy', $desk) }}" onsubmit="return confirm('{{ __('Delete desk?') }}');">
                @csrf @method('DELETE')
                <x-danger-button type="submit">{{ __('Delete desk') }}</x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Departments') }}</h2>
            <a href="{{ route('admin.departments.create') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Add') }}</a>
        </div>
    </x-slot>
    <div class="py-10 max-w-3xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-900 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
        @endif
        <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
            @foreach ($departments as $d)
                <div class="p-4 flex justify-between items-center">
                    <div>
                        <p class="font-medium">{{ $d->name }}</p>
                        <p class="text-xs text-gray-500">{{ $d->code }}</p>
                    </div>
                    <div class="space-x-3 text-sm">
                        <a href="{{ route('admin.departments.edit', $d) }}" class="text-gray-700 hover:text-gray-900">{{ __('Edit') }}</a>
                        <form action="{{ route('admin.departments.destroy', $d) }}" method="post" class="inline" onsubmit="return confirm('{{ __('Delete?') }}');">@csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>

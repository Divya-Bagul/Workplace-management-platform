<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Buildings') }}</h2>
            <div class="flex flex-wrap gap-3 text-sm">
                <a href="{{ route('admin.departments.index') }}" class="text-gray-600 hover:text-gray-900">{{ __('Departments') }}</a>
                <a href="{{ route('admin.buildings.create') }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Add building') }}</a>
            </div>
        </div>
    </x-slot>
    <div class="py-10 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if (session('status'))
            <div class="bg-green-50 border border-green-200 text-green-900 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
        @endif
        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-2">{{ __('Name') }}</th>
                        <th class="px-4 py-2">{{ __('Floors') }}</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($buildings as $b)
                        <tr>
                            <td class="px-4 py-2 font-medium">{{ $b->name }}</td>
                            <td class="px-4 py-2">{{ $b->floors_count }}</td>
                            <td class="px-4 py-2 text-right space-x-3">
                                <a href="{{ route('admin.buildings.floors.create', $b) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Add floor') }}</a>
                                <a href="{{ route('admin.buildings.edit', $b) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <form action="{{ route('admin.buildings.destroy', $b) }}" method="post" class="inline" onsubmit="return confirm('{{ __('Delete building?') }}');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @foreach ($b->floors as $f)
                            <tr class="bg-gray-50/60">
                                <td class="px-4 py-2 pl-8 text-gray-700">{{ $f->name }} <span class="text-gray-400">({{ __('level') }} {{ $f->level }})</span></td>
                                <td class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right space-x-3">
                                    <a href="{{ route('admin.buildings.floors.edit', [$b, $f]) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit floor') }}</a>
                                    <form action="{{ route('admin.buildings.floors.destroy', [$b, $f]) }}" method="post" class="inline" onsubmit="return confirm('{{ __('Delete floor?') }}');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

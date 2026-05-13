<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Desks') }}</h2>
            @hasanyrole('admin')
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('desks.create') }}" class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('Add desk') }}</a>
                </div>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-900 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex justify-between">
                    <span class="text-gray-600">{{ __('Available') }}</span>
                    <span class="font-semibold">{{ $counts['available'] }}</span>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex justify-between">
                    <span class="text-gray-600">{{ __('Occupied') }}</span>
                    <span class="font-semibold">{{ $counts['occupied'] }}</span>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-4 flex justify-between">
                    <span class="text-gray-600">{{ __('Reserved') }}</span>
                    <span class="font-semibold">{{ $counts['reserved'] }}</span>
                </div>
            </div>

            <form method="get" class="flex flex-wrap gap-2 items-center text-sm">
                <label class="text-gray-600">{{ __('Filter') }}</label>
                <select name="status" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (['available', 'occupied', 'reserved'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left text-gray-600">
                            <tr>
                                <th class="px-3 py-2">{{ __('Code') }}</th>
                                <th class="px-3 py-2">{{ __('Building') }}</th>
                                <th class="px-3 py-2">{{ __('Floor') }}</th>
                                <th class="px-3 py-2">{{ __('Status') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($desks as $desk)
                                <tr>
                                    <td class="px-3 py-2 font-medium">{{ $desk->code }}</td>
                                    <td class="px-3 py-2">{{ $desk->floor?->building?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $desk->floor?->name ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $desk->status }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('desks.show', $desk) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('History') }}</a>
                                        @hasanyrole('admin')
                                            <a href="{{ route('desks.edit', $desk) }}" class="ml-3 text-gray-700 hover:text-gray-900">{{ __('Edit') }}</a>
                                        @endhasanyrole
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

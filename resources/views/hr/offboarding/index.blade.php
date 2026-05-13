<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Offboarding') }}</h2>
            @hasanyrole('admin|hr')
                <a href="{{ route('offboarding.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('New offboarding') }}</a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Employee') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Last day') }}</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                                <th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($requests as $req)
                                <tr>
                                    <td class="px-3 py-2">{{ $req->employee?->name }} ({{ $req->employee?->employee_code }})</td>
                                    <td class="px-3 py-2">{{ $req->last_working_day?->format('Y-m-d') }}</td>
                                    <td class="px-3 py-2">{{ $req->status }}</td>
                                    <td class="px-3 py-2 text-right">
                                        <a href="{{ route('offboarding.show', $req) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Open') }}</a>
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

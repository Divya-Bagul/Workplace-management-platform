<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Desk') }} {{ $desk->code }}</h2>
            <a href="{{ route('desks.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">{{ __('Back') }}</a>
        </div>
    </x-slot>
    <div class="py-10 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm space-y-1">
            <p><span class="text-gray-500">{{ __('Building / floor') }}:</span> {{ $desk->floor?->building?->name }} / {{ $desk->floor?->name }}</p>
            <p><span class="text-gray-500">{{ __('Status') }}:</span> {{ $desk->status }}</p>
            @if ($desk->notes)
                <p><span class="text-gray-500">{{ __('Notes') }}:</span> {{ $desk->notes }}</p>
            @endif
        </div>
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <h3 class="font-medium text-gray-900 mb-3">{{ __('Allocation history') }}</h3>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-3 py-2">{{ __('Employee') }}</th>
                        <th class="px-3 py-2">{{ __('From') }}</th>
                        <th class="px-3 py-2">{{ __('To') }}</th>
                        <th class="px-3 py-2">{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($desk->allocations as $a)
                        <tr>
                            <td class="px-3 py-2">{{ $a->employee?->name }} ({{ $a->employee?->employee_code }})</td>
                            <td class="px-3 py-2">{{ $a->valid_from?->format('Y-m-d') }}</td>
                            <td class="px-3 py-2">{{ $a->valid_to?->format('Y-m-d') ?? __('Open') }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $a->notes }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-4 text-gray-500">{{ __('No allocations yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>

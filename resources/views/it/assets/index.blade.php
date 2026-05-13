<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('IT assets') }}</h2>
            <a href="{{ route('assets.create') }}" class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('Register asset') }}</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-900 px-4 py-3 rounded-lg text-sm">{{ session('status') }}</div>
            @endif

            <form method="get" class="text-sm flex items-center gap-2">
                <span class="text-gray-600">{{ __('Status') }}</span>
                <select name="status" class="border-gray-300 rounded-md shadow-sm" onchange="this.form.submit()">
                    <option value="">{{ __('All') }}</option>
                    @foreach (['in_stock', 'assigned', 'repair', 'retired'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-4 overflow-x-auto space-y-6">
                    @foreach ($assets as $asset)
                        <div class="border border-gray-100 rounded-lg p-4 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                            <div class="text-sm space-y-1">
                                <p class="font-semibold text-gray-900">{{ $asset->asset_tag }} <span class="text-gray-500 font-normal">· {{ $asset->assetType?->name }}</span></p>
                                <p class="text-gray-600">{{ __('Serial') }}: {{ $asset->serial_number ?? '—' }}</p>
                                <p><span class="text-gray-500">{{ __('Status') }}:</span> <span class="font-medium">{{ $asset->status }}</span></p>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3 text-sm">
                                @if ($asset->status === 'in_stock')
                                    <form method="post" action="{{ route('assets.assign', $asset) }}" class="flex flex-col gap-2 min-w-[220px]">
                                        @csrf
                                        <select name="employee_id" class="border-gray-300 rounded-md shadow-sm" required>
                                            <option value="">{{ __('Assign to…') }}</option>
                                            @foreach ($employees as $e)
                                                <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->employee_code }})</option>
                                            @endforeach
                                        </select>
                                        <x-primary-button type="submit" class="justify-center">{{ __('Assign') }}</x-primary-button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('assets.return', $asset) }}">
                                        @csrf
                                        <x-secondary-button type="submit">{{ __('Mark returned') }}</x-secondary-button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if ($assets->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('No assets match this filter.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

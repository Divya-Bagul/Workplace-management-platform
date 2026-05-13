<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Onboarding') }}</h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4 gap-2">
                @hasanyrole('admin|hr')
                    <a href="{{ route('onboarding.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('New onboarding') }}</a>
                @endhasanyrole
            </div>
                <div class="p-6 overflow-x-auto">
                    <table id="onboarding-table" class="display compact stripe w-full text-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Desk') }}</th>
                                <th>{{ __('IT complete') }}</th>
                                <th>{{ __('Asset providing') }}</th>
                                <th>{{ __('Updated') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $req)
                                <tr>
                                    <td>{{ $req->employee?->name ?? '—' }} ({{ $req->employee?->employee_code ?? '—' }})</td>
                                    <td>{{ $req->status }}</td>
                                    <td>
                                        @if ($req->desk)
                                            {{ $req->desk->code }} · {{ $req->desk->floor?->building?->name ?? '' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $req->it_setup_completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    <td>
                                        @if ($req->tracksAssetProvision())
                                            {{ __($req->assetProvisionStatus()) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $req->updated_at->format('Y-m-d H:i') }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('onboarding.show', $req) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Open') }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.jQuery && jQuery.fn.DataTable) {
                    jQuery('#onboarding-table').DataTable({
                        pageLength: 25,
                        order: [[5, 'desc']],
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>

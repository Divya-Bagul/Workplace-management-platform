<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Employees') }}</h2>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endpush

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-end mb-4">
                @hasanyrole('admin|hr')
                    <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('Add employee') }}</a>
                @endhasanyrole
            </div>
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6 overflow-x-auto">
                    <table id="employees-table" class="display compact stripe w-full text-sm" style="width:100%">
                        <thead>
                            <tr>
                                <th>{{ __('Code') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Department') }}</th>
                                <th>{{ __('Designation') }}</th>
                                <th>{{ __('Location') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                <tr>
                                    <td>{{ $employee->employee_code }}</td>
                                    <td>{{ $employee->name }}</td>
                                    <td>{{ $employee->department?->name ?? '—' }}</td>
                                    <td>{{ $employee->designation ?? '—' }}</td>
                                    <td>
                                        @if ($employee->building || $employee->floor)
                                            {{ $employee->building?->name }}{{ $employee->floor ? ' · '.$employee->floor->name : '' }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $employee->employment_status }}</td>
                                    <td class="text-right">
                                        <a href="{{ route('employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
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
                    jQuery('#employees-table').DataTable({
                        pageLength: 25,
                        order: [[1, 'asc']],
                    });
                }
            });
        </script>
    @endpush
</x-app-layout>

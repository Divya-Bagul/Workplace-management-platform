<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Audit log') }}</h2></x-slot>
    <div class="py-10 max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
            <table class="min-w-full text-xs sm:text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-3 py-2">{{ __('When') }}</th>
                        <th class="px-3 py-2">{{ __('User') }}</th>
                        <th class="px-3 py-2">{{ __('Action') }}</th>
                        <th class="px-3 py-2">{{ __('Subject') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($logs as $log)
                        <tr>
                            <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-2">{{ $log->user?->email ?? '—' }}</td>
                            <td class="px-3 py-2 font-mono">{{ $log->action }}</td>
                            <td class="px-3 py-2">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>

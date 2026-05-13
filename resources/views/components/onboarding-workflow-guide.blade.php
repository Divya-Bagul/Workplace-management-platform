@php($guide = $onboarding->workflowGuide(auth()->user()))
<div id="onboarding-flow" class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4 scroll-mt-24">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <h3 class="font-medium text-gray-900">{{ __('Onboarding flow') }}</h3>
            <p class="mt-1 text-sm text-gray-600">{{ __('Follow the modules in order. The highlighted step is where the request is now.') }}</p>
        </div>
        @if ($guide['next'])
            <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-sm text-indigo-950 max-w-xl">
                <p class="font-semibold">{{ __('Next for :role', ['role' => $guide['next']['role']]) }}</p>
                <p class="mt-1">{{ $guide['next']['label'] }}</p>
                <p class="mt-2 text-indigo-800">{{ __('Go to') }}: {{ $guide['next']['module'] }}</p>
                @isset($guide['next']['route'])
                    <a href="{{ $guide['next']['route'] }}" class="mt-2 inline-flex text-indigo-700 font-medium hover:text-indigo-900">{{ __('Open module') }}</a>
                @endisset
            </div>
        @endif
    </div>

    <ol class="grid grid-cols-1 md:grid-cols-5 gap-3">
        @foreach ($guide['steps'] as $step)
            <li @class([
                'rounded-lg border p-3 text-sm',
                'border-green-200 bg-green-50 text-green-900' => $step['state'] === 'complete',
                'border-indigo-300 bg-indigo-50 text-indigo-950 ring-2 ring-indigo-200' => $step['state'] === 'current',
                'border-gray-200 bg-gray-50 text-gray-600' => $step['state'] === 'upcoming',
                'border-red-200 bg-red-50 text-red-900' => $step['state'] === 'cancelled',
            ])>
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ $step['module'] }}</p>
                <p class="mt-1 font-medium">{{ $step['title'] }}</p>
                <p class="mt-2 text-xs">
                    @if ($step['state'] === 'complete')
                        {{ __('Done') }}
                    @elseif ($step['state'] === 'current')
                        {{ __('Current step') }}
                    @elseif ($step['state'] === 'cancelled')
                        {{ __('Cancelled') }}
                    @else
                        {{ __('Upcoming') }}
                    @endif
                </p>
            </li>
        @endforeach
    </ol>
</div>

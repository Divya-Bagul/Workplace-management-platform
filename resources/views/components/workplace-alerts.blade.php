@auth
    @hasanyrole('admin|hr|it')
        <div id="workplace-live-toast" class="hidden fixed bottom-4 right-4 z-50 max-w-sm rounded-lg border border-indigo-100 bg-white p-4 text-sm text-gray-800 shadow-lg" role="status"></div>

        <div class="hidden sm:flex sm:items-center sm:ms-4 relative" x-data="{ open: false }">
            <button type="button" @click="open = ! open" class="relative inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
                <span>{{ __('Notifications') }}</span>
                <span id="workplace-notification-count" class="hidden absolute -top-1 -right-1 inline-flex min-h-5 min-w-5 items-center justify-center rounded-full bg-indigo-600 px-1 text-xs font-semibold text-white"></span>
            </button>
            <div x-show="open" @click.outside="open = false" class="absolute right-0 top-12 z-50 w-80 rounded-lg border border-gray-200 bg-white shadow-lg">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <p class="text-sm font-medium text-gray-900">{{ __('In-app alerts') }}</p>
                    <button type="button" id="workplace-enable-browser-notifications" class="text-xs text-indigo-700 hover:text-indigo-900">{{ __('Enable browser alerts') }}</button>
                </div>
                <div id="workplace-notification-list" class="max-h-80 overflow-y-auto"></div>
            </div>
        </div>
    @endhasanyrole
@endauth

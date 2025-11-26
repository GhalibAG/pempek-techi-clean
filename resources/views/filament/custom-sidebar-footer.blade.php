<div class="px-4 py-4 border-t border-gray-200 dark:border-gray-700 bg-white/50 dark:bg-gray-900/50">

    <div class="flex items-center gap-3 mb-4">
        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold text-lg">
            {{ substr(auth()->user()->name, 0, 1) }}
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                {{ auth()->user()->name }}
            </p>

            {{-- Logic Badge: Beda Warna untuk Owner & Admin --}}
            @if (auth()->user()->role === 'owner')
                <span
                    class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">
                    👑 OWNER
                </span>
            @else
                <span
                    class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                    🛡️ ADMIN
                </span>
            @endif
        </div>
    </div>

    <form action="{{ filament()->getLogoutUrl() }}" method="post" class="w-full">
        @csrf
        <button type="submit"
            class="flex items-center justify-center w-full gap-2 rounded-lg px-3 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 transition-colors">
            <x-filament::icon icon="heroicon-o-arrow-left-on-rectangle" class="h-4 w-4" />
            Sign Out
        </button>
    </form>
</div>

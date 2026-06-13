{{-- Mobile navigation drawer. Toggled and Esc-closed by app.js. --}}
<div class="fixed inset-0 z-[150] hidden" id="mobile-drawer" data-drawer>
    <div class="absolute inset-0 bg-navy-900/50" data-drawer-close aria-hidden="true"></div>

    <div
        class="absolute inset-y-0 end-0 flex w-[min(86%,360px)] flex-col gap-1.5 overflow-auto bg-navy-700 p-5 text-white"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('nav.menu_label') }}"
        data-drawer-panel
    >
        <div class="mb-3.5 flex items-center justify-between">
            <span class="flex items-center gap-2.5 text-[1.2rem] font-extrabold text-white">
                <x-layout.brand-mark />
                {{ config('site.brand') }}
            </span>
            <button
                type="button"
                class="h-10 w-10 rounded-[10px] border border-white/20 bg-transparent text-2xl leading-none text-white"
                data-drawer-close
                aria-label="{{ __('nav.close_menu') }}"
            >&times;</button>
        </div>

        @foreach (\App\Support\Navigation::primary() as $item)
            <a href="{{ $item['url'] }}" class="border-b border-white/10 py-4 text-[1.3rem] font-bold">{{ $item['label'] }}</a>
        @endforeach

        <x-ui.button :href="\App\Support\Navigation::contactUrl()" variant="accent" :block="true" class="mt-4">
            {{ __('nav.cta_long') }}
        </x-ui.button>
    </div>
</div>

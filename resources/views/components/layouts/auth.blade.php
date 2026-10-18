<x-layouts.base :title="$title ?? null">
    <div class="relative flex min-h-dvh flex-col items-center justify-center overflow-hidden px-4 py-10">
        {{-- Formes douces décoratives --}}
        <div aria-hidden="true" class="pointer-events-none absolute -top-24 -start-24 size-72 rounded-full bg-terra-200/50 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-24 -end-16 size-80 rounded-full bg-honey-200/60 blur-3xl"></div>

        <div class="relative w-full max-w-md">
            <div class="mb-6 flex items-center justify-between">
                <a href="{{ route('home') }}" class="rounded-xl focus-visible:outline-2 focus-visible:outline-terra-500">
                    <x-brand.logo />
                </a>
                <x-locale-switcher />
            </div>

            <div class="card p-6 sm:p-8">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-sm">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1 font-semibold text-muted hover:text-terra-700">
                    <x-icon name="chevron-left" class="flip-rtl size-4" /> {{ __('auth.back_home') }}
                </a>
            </p>
        </div>
    </div>
</x-layouts.base>

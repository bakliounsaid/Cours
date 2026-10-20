<footer class="bg-sand-200/60">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr]">
        <div>
            <x-brand.logo tagline />
            <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted">{{ __('home.footer.about') }}</p>
        </div>

        <div>
            <h2 class="text-sm font-extrabold tracking-wider text-muted uppercase">{{ __('home.footer.contact') }}</h2>
            <ul class="mt-4 space-y-2 text-sm font-semibold text-olive-700">
                <li><a href="{{ $contact['phone_href'] }}" class="hover:text-terra-700" dir="ltr">{{ $contact['phone'] }}</a></li>
                <li><a href="mailto:{{ $contact['email'] }}" class="hover:text-terra-700">{{ $contact['email'] }}</a></li>
            </ul>
        </div>

        <div>
            <h2 class="text-sm font-extrabold tracking-wider text-muted uppercase">{{ __('app.name') }}</h2>
            <ul class="mt-4 space-y-2 text-sm font-semibold text-olive-700">
                <li><a href="#matieres" class="hover:text-terra-700">{{ __('home.nav.subjects') }}</a></li>
                <li><a href="#horaires" class="hover:text-terra-700">{{ __('home.nav.hours') }}</a></li>
                <li><a href="#contact" class="hover:text-terra-700">{{ __('home.enroll_cta') }}</a></li>
            </ul>
        </div>
    </div>

    <div class="border-t border-olive-100/70">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-5 text-xs text-subtle sm:flex-row sm:px-6">
            <p>© {{ now()->year }} {{ __('app.name') }} · {{ __('app.slogan') }} · {{ __('home.footer.rights') }}</p>
            <a href="{{ route('teacher.login') }}" class="inline-flex items-center gap-1 hover:text-muted">
                <x-icon name="academic-cap" class="size-3.5" /> {{ __('home.footer.teacher_space') }}
            </a>
        </div>
    </div>
</footer>

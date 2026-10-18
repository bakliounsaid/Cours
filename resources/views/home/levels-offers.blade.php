<section class="bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        @include('home.section-heading', ['eyebrow' => __('home.levels.eyebrow'), 'title' => __('home.levels.title')])

        <div class="mt-12 grid gap-6 md:grid-cols-2">
            @foreach ($cycles as $cycle)
                @php($isPrimary = $cycle === \App\Enums\Cycle::Primary)
                <article x-reveal="{{ $loop->index * 100 }}" @class([
                    'relative overflow-hidden rounded-card p-8',
                    'bg-honey-100' => $isPrimary,
                    'bg-olive-700 text-white' => ! $isPrimary,
                ])>
                    <div aria-hidden="true" @class(['absolute -end-10 -bottom-10 size-40 rounded-full', 'bg-honey-200' => $isPrimary, 'bg-olive-600' => ! $isPrimary])></div>
                    <div class="relative">
                        <span @class(['flex size-12 items-center justify-center rounded-2xl', 'bg-white text-honey-700' => $isPrimary, 'bg-terra-600 text-white' => ! $isPrimary])>
                            <x-icon :name="$isPrimary ? 'sun' : 'academic-cap'" class="size-6" />
                        </span>
                        <h3 @class(['mt-5 text-2xl font-extrabold', 'text-white' => ! $isPrimary])>{{ $cycle->label() }}</h3>
                        <p @class(['mt-2', 'text-muted' => $isPrimary, 'text-olive-100' => ! $isPrimary])>{{ $isPrimary ? __('home.levels.primary_text') : __('home.levels.middle_text') }}</p>
                        <ul class="mt-5 flex flex-wrap gap-2">
                            @foreach ($levelsByCycle[$cycle->value] ?? [] as $level)
                                <li @class(['rounded-full px-3 py-1 text-sm font-bold', 'bg-white text-olive-700' => $isPrimary, 'bg-olive-600 text-white' => ! $isPrimary])>{{ $level->name }}</li>
                            @endforeach
                        </ul>
                    </div>
                </article>
            @endforeach
        </div>

        <div id="formules" class="scroll-mt-24 pt-24">
            @include('home.section-heading', ['eyebrow' => __('home.offers.eyebrow'), 'title' => __('home.offers.title')])

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                @foreach (['group' => 'user-group', 'individual' => 'user'] as $offer => $icon)
                    @php($highlight = $offer === 'group')
                    <article x-reveal="{{ $loop->index * 100 }}" @class([
                        'relative rounded-card p-8 ring-1',
                        'bg-terra-50 ring-terra-200' => $highlight,
                        'bg-sand-50 ring-olive-100' => ! $highlight,
                    ])>
                        @if ($highlight)
                            <span class="absolute -top-3 end-6 rounded-full bg-terra-600 px-3 py-1 text-xs font-extrabold text-white shadow-soft">{{ __('home.offers.popular') }}</span>
                        @endif
                        <span @class(['flex size-12 items-center justify-center rounded-2xl', 'bg-terra-600 text-white' => $highlight, 'bg-olive-700 text-white' => ! $highlight])>
                            <x-icon :name="$icon" class="size-6" />
                        </span>
                        <h3 class="mt-5 text-2xl font-extrabold">{{ __('home.offers.'.$offer.'_title') }}</h3>
                        <p class="mt-2 text-muted">{{ __('home.offers.'.$offer.'_text') }}</p>
                        <ul class="mt-6 space-y-3">
                            @foreach (__('home.offers.'.$offer.'_points') as $point)
                                <li class="flex items-center gap-3 font-semibold text-olive-700">
                                    <span class="flex size-6 shrink-0 items-center justify-center rounded-full bg-white text-emerald-600 ring-1 ring-emerald-200"><x-icon name="check" class="size-3.5" /></span>
                                    {{ $point }}
                                </li>
                            @endforeach
                        </ul>
                        <a href="#contact" @class(['mt-8 w-full', 'btn-primary' => $highlight, 'btn-secondary' => ! $highlight])>{{ __('home.enroll_cta') }}</a>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

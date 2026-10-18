<section id="matieres" class="relative scroll-mt-20 py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        @include('home.section-heading', ['eyebrow' => __('home.subjects.eyebrow'), 'title' => __('home.subjects.title'), 'subtitle' => __('home.subjects.subtitle')])

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($subjects as $subject)
                <article x-reveal="{{ $loop->index * 80 }}"
                         style="--subject: {{ $subject->color }}"
                         class="group relative overflow-hidden rounded-card bg-white p-6 shadow-soft ring-1 ring-olive-100/60 transition hover:-translate-y-1 hover:shadow-lift">
                    <div aria-hidden="true" class="absolute -end-8 -top-8 size-28 rounded-full bg-(--subject) opacity-10 transition group-hover:scale-125"></div>
                    <span class="relative flex size-14 items-center justify-center rounded-2xl bg-(--subject) text-white shadow-soft">
                        <x-icon :name="$subject->icon" class="size-7" />
                    </span>
                    <h3 class="relative mt-5 text-xl font-bold">{{ $subject->name }}</h3>
                    @if (\Illuminate\Support\Facades\Lang::has('home.subjects.descriptions.'.$subject->slug))
                        <p class="relative mt-2 text-sm leading-relaxed text-muted">{{ __('home.subjects.descriptions.'.$subject->slug) }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>

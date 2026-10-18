<section id="pourquoi" class="scroll-mt-20 bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        @include('home.section-heading', ['eyebrow' => __('home.why.eyebrow'), 'title' => __('home.why.title'), 'subtitle' => __('home.why.subtitle')])

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($features as $feature)
                @php($tone = ['bg-terra-100 text-terra-700', 'bg-honey-100 text-honey-700', 'bg-olive-50 text-muted', 'bg-emerald-100 text-emerald-600'][$loop->index % 4])
                <article x-reveal="{{ $loop->index * 90 }}" class="group rounded-card bg-sand-50 p-7 ring-1 ring-olive-100/60 transition hover:-translate-y-1 hover:bg-white hover:shadow-lift">
                    <span class="flex size-14 items-center justify-center rounded-2xl {{ $tone }} transition group-hover:scale-110 group-hover:-rotate-6">
                        <x-icon :name="$feature['icon']" class="size-7" />
                    </span>
                    <h3 class="mt-5 text-xl font-bold">{{ $feature['title'] }}</h3>
                    <p class="mt-2 leading-relaxed text-muted">{{ $feature['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

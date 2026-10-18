{{-- Titre de section : $eyebrow, $title, $subtitle (facultatif) --}}
<div class="mx-auto max-w-2xl text-center">
    <p x-reveal class="inline-flex items-center gap-2 text-sm font-extrabold tracking-wider text-terra-700 uppercase">
        <span class="h-0.5 w-6 rounded-full bg-terra-300"></span>{{ $eyebrow }}<span class="h-0.5 w-6 rounded-full bg-terra-300"></span>
    </p>
    <h2 x-reveal="80" class="mt-3 text-3xl font-extrabold text-balance sm:text-4xl">{{ $title }}</h2>
    @isset($subtitle)
        <p x-reveal="160" class="mt-4 text-lg text-muted">{{ $subtitle }}</p>
    @endisset
</div>

@php use App\Support\Format; @endphp

<div class="space-y-6">
    {{-- Bandeau d'accueil --}}
    <section class="animate-fade-up relative overflow-hidden rounded-card bg-olive-700 p-5 text-white shadow-lift sm:p-6">
        <div class="pointer-events-none absolute -end-10 -top-10 size-40 rounded-full bg-terra-500/30 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-12 start-10 size-32 rounded-full bg-honey-400/20 blur-2xl"></div>

        <p class="relative text-sm font-semibold text-olive-100 capitalize">{{ Format::dayDate(now(), withYear: true) }}</p>
        <h1 class="relative mt-1 text-2xl font-extrabold text-white sm:text-3xl">{{ $greeting }}, {{ str(auth()->user()->name)->before(' ') }} 👋</h1>
        <p class="relative mt-1 text-olive-100">{{ trans_choice('teacher.today.summary', $today->count(), ['count' => $today->count()]) }}</p>

        <div class="relative mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach ([
                ['groups', 'user-group', $stats['groups'], route('teacher.groups')],
                ['students', 'users', $stats['students'], route('teacher.students')],
                ['month_lessons', 'check-circle', $stats['month_lessons'], null],
                ['earned', 'banknotes', $stats['earned'], null],
            ] as [$key, $icon, $value, $href])
                <a @if ($href) href="{{ $href }}" wire:navigate @endif
                   @class(['group rounded-2xl bg-white/10 p-3 ring-1 ring-white/10 backdrop-blur transition', 'hover:-translate-y-0.5 hover:bg-white/15' => $href])>
                    <span class="flex items-center gap-1.5 text-xs font-semibold text-olive-100">
                        <x-icon :name="$icon" class="size-4" /> {{ __('teacher.today.stats.'.$key) }}
                    </span>
                    <span class="mt-1 block font-display text-2xl font-extrabold text-white" dir="ltr">
                        @if ($key === 'earned')
                            <span x-data x-count-up="{{ $value }}">{{ number_format($value, 0, ',', ' ') }}</span>
                            <span class="text-sm font-bold text-olive-100">{{ __('app.currency') }}</span>
                        @else
                            <span x-data x-count-up="{{ $value }}">{{ $value }}</span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Raccourcis --}}
    <section class="animate-fade-up grid grid-cols-2 gap-3 [animation-delay:80ms]">
        <a href="{{ route('teacher.groups', ['nouveau' => 1]) }}" wire:navigate class="card card-hover flex items-center gap-3 p-4">
            <span class="flex size-11 items-center justify-center rounded-2xl bg-terra-50 text-terra-700"><x-icon name="plus" /></span>
            <span class="text-sm font-bold text-olive-700">{{ __('teacher.groups.add') }}</span>
        </a>
        <a href="{{ route('teacher.students', ['nouveau' => 1]) }}" wire:navigate class="card card-hover flex items-center gap-3 p-4">
            <span class="flex size-11 items-center justify-center rounded-2xl bg-honey-100 text-honey-700"><x-icon name="user" /></span>
            <span class="text-sm font-bold text-olive-700">{{ __('teacher.students.add') }}</span>
        </a>
    </section>

    {{-- Séances du jour --}}
    <section class="animate-fade-up [animation-delay:140ms]" aria-labelledby="today-lessons">
        <h2 id="today-lessons" class="mb-3 text-lg font-bold">{{ __('teacher.today.lessons') }}</h2>

        @forelse ($today as $lesson)
            @include('livewire.teacher.partials.lesson-card', ['lesson' => $lesson, 'live' => true])
        @empty
            <div class="card flex flex-col items-center gap-2 p-8 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-sage-100 text-sage-700"><x-icon name="sun" class="size-7" /></span>
                <p class="font-bold text-olive-700">{{ __('teacher.today.no_lessons') }}</p>
                <p class="text-sm text-subtle">{{ __('teacher.today.no_lessons_hint') }}</p>
            </div>
        @endforelse
    </section>

    {{-- Prochaines séances --}}
    @if ($upcoming->isNotEmpty())
        <section class="animate-fade-up [animation-delay:200ms]" aria-labelledby="upcoming-lessons">
            <h2 id="upcoming-lessons" class="mb-3 text-lg font-bold">{{ __('teacher.today.upcoming') }}</h2>
            @foreach ($upcoming as $lesson)
                @include('livewire.teacher.partials.lesson-card', ['lesson' => $lesson, 'live' => false])
            @endforeach
        </section>
    @endif

    {{-- Mes tarifs --}}
    <section class="animate-fade-up card p-5 [animation-delay:260ms]" aria-labelledby="my-rates">
        <h2 id="my-rates" class="flex items-center gap-2 text-lg font-bold"><x-icon name="banknotes" class="text-terra-600" /> {{ __('teacher.rates.title') }}</h2>
        <p class="mt-0.5 text-sm text-subtle">{{ __('teacher.rates.hint') }}</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-sage-50 p-4 ring-1 ring-sage-200">
                <p class="flex items-center gap-1.5 text-sm font-semibold text-sage-700"><x-icon name="user-group" class="size-4" /> {{ __('teacher.rates.group') }}</p>
                <p class="mt-1 font-display text-2xl font-extrabold text-ink" dir="ltr">{{ Format::money($this->teacher->rate_group) }}</p>
                <p class="text-xs text-subtle">{{ __('teacher.rates.per_lesson') }}</p>
            </div>
            <div class="rounded-2xl bg-honey-100/70 p-4 ring-1 ring-honey-200">
                <p class="flex items-center gap-1.5 text-sm font-semibold text-honey-700"><x-icon name="user" class="size-4" /> {{ __('teacher.rates.individual') }}</p>
                <p class="mt-1 font-display text-2xl font-extrabold text-ink" dir="ltr">{{ Format::money($this->teacher->rate_individual) }}</p>
                <p class="text-xs text-subtle">{{ __('teacher.rates.per_lesson') }}</p>
            </div>
        </div>
    </section>
</div>

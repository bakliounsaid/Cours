<div>
    <a href="{{ route('admin.teachers') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-terra-700">
        <x-icon name="chevron-left" class="flip-rtl size-4" /> {{ __('admin.nav.teachers') }}
    </a>

    {{-- En-tête de la fiche --}}
    <div class="card mb-6 flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
        <span class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-olive-700 font-display text-2xl font-extrabold text-white">{{ $teacher->user->initials() }}</span>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-extrabold">{{ $teacher->user->name }}</h1>
                <span @class(['badge', 'bg-olive-50 text-olive-700' => $teacher->is_active, 'bg-sand-200 text-muted' => ! $teacher->is_active])>
                    {{ $teacher->is_active ? __('ui.active') : __('ui.inactive') }}
                </span>
            </div>
            <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted">
                <span class="inline-flex items-center gap-1" dir="ltr"><x-icon name="inbox" class="size-4" /> {{ $teacher->user->email }}</span>
                @if ($phone)
                    <span class="inline-flex items-center gap-1" dir="ltr"><x-icon name="phone" class="size-4" /> {{ $phone }}</span>
                @endif
                <span class="inline-flex items-center gap-1"><x-icon name="user-group" class="size-4" /> {{ __('admin.teachers.rate_group_value', ['amount' => \App\Support\Format::money($teacher->rate_group)]) }}</span>
                <span class="inline-flex items-center gap-1"><x-icon name="user" class="size-4" /> {{ __('admin.teachers.rate_individual_value', ['amount' => \App\Support\Format::money($teacher->rate_individual)]) }}</span>
            </p>
            <div class="mt-3 flex flex-wrap gap-1.5">
                @foreach ($teacher->subjects as $subject)
                    <span class="badge text-white" style="background: {{ $subject->color }}">{{ $subject->name }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Chiffres du mois --}}
    <h2 class="mb-3 text-lg font-bold">{{ __('admin.teachers.this_month', ['month' => \App\Support\Format::month(now())]) }}</h2>
    <div class="mb-8 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
        <x-ui.stat-card :label="__('admin.teachers.stats.done')" :value="$stats['done']" icon="check-circle" tone="green" />
        <x-ui.stat-card :label="__('admin.teachers.stats.upcoming')" :value="$stats['upcoming']" icon="calendar" tone="navy" />
        <x-ui.stat-card :label="__('admin.teachers.stats.cancelled')" :value="$stats['cancelled']" icon="x-circle" tone="red" />
        <x-ui.stat-card :label="__('admin.teachers.stats.earned')" :value="\App\Support\Format::money($stats['earned'])" icon="banknotes" tone="sun" />
    </div>

    {{-- Historique --}}
    <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
        <h2 class="text-lg font-bold">{{ __('admin.teachers.history') }}</h2>
        <div class="flex flex-wrap items-end gap-2">
            @foreach (['this_month', 'last_month', 'upcoming', 'all'] as $period)
                <button type="button" wire:click="period('{{ $period }}')" class="btn-ghost btn-sm">{{ __('admin.teachers.periods.'.$period) }}</button>
            @endforeach
            <label class="text-xs font-semibold text-muted">
                {{ __('ui.from') }}
                <input type="date" wire:model.live="from" class="input mt-1 w-auto py-1.5 text-sm">
            </label>
            <label class="text-xs font-semibold text-muted">
                {{ __('ui.to') }}
                <input type="date" wire:model.live="to" class="input mt-1 w-auto py-1.5 text-sm">
            </label>
        </div>
    </div>

    @include('tables.data-table')
</div>

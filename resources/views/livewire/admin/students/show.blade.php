<div class="space-y-8">
    <div>
        <a href="{{ route('admin.students.index') }}" wire:navigate class="mb-4 inline-flex items-center gap-1 text-sm font-semibold text-muted hover:text-terra-700">
            <x-icon name="chevron-left" class="flip-rtl size-4" /> {{ __('admin.nav.students') }}
        </a>

        {{-- En-tête de la fiche --}}
        <div class="card flex flex-col gap-5 p-6 sm:flex-row sm:items-center">
            <span class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-sage-100 font-display text-2xl font-extrabold text-sage-700">{{ $initials }}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-extrabold">{{ $student->full_name }}</h1>
                    <span @class(['badge', 'bg-olive-50 text-olive-700' => $student->is_active, 'bg-sand-200 text-muted' => ! $student->is_active])>
                        {{ $student->is_active ? __('ui.active') : __('ui.inactive') }}
                    </span>
                    <span class="badge {{ $status['classes'] }}"><x-icon name="banknotes" class="size-3.5" /> {{ $status['label'] }}</span>
                </div>
                <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted">
                    <span class="inline-flex items-center gap-1"><x-icon name="layers" class="size-4" /> {{ $student->level->name }}</span>
                    <a href="{{ $phoneHref }}" class="inline-flex items-center gap-1 hover:text-terra-700" dir="ltr"><x-icon name="phone" class="size-4" /> {{ $phone }}</a>
                    <span class="inline-flex items-center gap-1"><x-icon name="calendar" class="size-4" /> {{ __('admin.students.enrolled_at', ['date' => $enrolledAt]) }}</span>
                </p>
                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach ($groups as $group)
                        <span class="badge text-white" style="background: {{ $group['color'] }}">{{ $group['name'] }} · {{ $group['teacher'] }}</span>
                    @endforeach
                    @if ($individualTeachers)
                        <span class="badge bg-honey-100 text-honey-700"><x-icon name="user" class="size-3.5" /> {{ __('admin.students.individual_with', ['names' => $individualTeachers]) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Statistiques --}}
    <section aria-labelledby="student-stats">
        <h2 id="student-stats" class="mb-3 text-lg font-bold">{{ __('admin.students.statistics') }}</h2>
        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-5">
            @foreach ($tiles as $tile)
                <x-ui.stat-card :label="$tile['label']" :value="$tile['value']" :icon="$tile['icon']" :tone="$tile['tone']" />
            @endforeach
        </div>
    </section>

    {{-- Cycle de séances par enseignant --}}
    <section aria-labelledby="student-cycles">
        <h2 id="student-cycles" class="mb-3 text-lg font-bold">{{ __('admin.students.cycles') }}</h2>
        <div class="card overflow-hidden">
            @forelse ($cycles as $cycle)
                <div class="flex flex-col gap-3 border-b border-olive-50 p-4 last:border-0 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-ink">{{ $cycle['teacher'] }}</p>
                        <p class="text-xs text-subtle">{{ __('admin.students.cycle_detail', ['attended' => $cycle['attended'], 'covered' => $cycle['covered']]) }}</p>
                    </div>
                    <div class="w-full sm:w-56">
                        <div class="mb-1 flex justify-between text-xs font-semibold">
                            <span class="text-muted">{{ __('teacher.payments.sessions') }}</span>
                            <span class="text-ink" dir="ltr">{{ $cycle['progress'] }}</span>
                        </div>
                        <div class="progress"><span style="width: {{ $cycle['percent'] }}%" class="{{ $cycle['barClasses'] }}"></span></div>
                    </div>
                    <span class="badge {{ $cycle['statusClasses'] }} self-start sm:self-center">{{ $cycle['status'] }}</span>
                </div>
            @empty
                <p class="p-6 text-center text-sm text-subtle">{{ __('admin.students.no_cycles') }}</p>
            @endforelse
        </div>
    </section>

    <div class="grid gap-8 xl:grid-cols-2">
        {{-- Paiements --}}
        <section aria-labelledby="student-payments">
            <h2 id="student-payments" class="mb-3 text-lg font-bold">{{ __('admin.students.payments') }}</h2>
            <ul class="card divide-y divide-olive-50 overflow-hidden">
                @forelse ($payments as $payment)
                    <li class="flex items-start gap-3 p-4">
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><x-icon name="banknotes" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex flex-wrap items-baseline justify-between gap-2">
                                <span class="font-display text-lg font-extrabold text-ink" dir="ltr">{{ $payment['amount'] }}</span>
                                <span class="text-xs text-subtle" dir="ltr">{{ $payment['date'] }}</span>
                            </span>
                            <span class="block text-sm text-muted">{{ $payment['lessons'] }} · {{ $payment['teacher'] }} · {{ $payment['method'] }}</span>
                            @if ($payment['recordedBy'])
                                <span class="block text-xs text-subtle">{{ __('admin.students.recorded_by', ['name' => $payment['recordedBy']]) }}</span>
                            @endif
                        </span>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-subtle">{{ __('admin.students.no_payments') }}</li>
                @endforelse
            </ul>
        </section>

        {{-- Dernières présences --}}
        <section aria-labelledby="student-attendance">
            <h2 id="student-attendance" class="mb-3 text-lg font-bold">{{ __('admin.students.recent_attendance') }}</h2>
            <ul class="card divide-y divide-olive-50 overflow-hidden">
                @forelse ($attendances as $attendance)
                    <li class="flex items-center gap-3 p-4">
                        <span class="w-24 shrink-0 text-xs" dir="ltr">
                            <span class="block font-bold text-olive-700">{{ $attendance['date'] }}</span>
                            <span class="block text-subtle">{{ $attendance['time'] }}</span>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-bold text-ink">{{ $attendance['lesson'] }}</span>
                            <span class="block truncate text-xs text-muted">{{ $attendance['subject'] }} · {{ $attendance['teacher'] }}</span>
                        </span>
                        <span class="badge {{ $attendance['statusClasses'] }}">{{ $attendance['status'] }}</span>
                    </li>
                @empty
                    <li class="p-6 text-center text-sm text-subtle">{{ __('admin.students.no_attendance') }}</li>
                @endforelse
            </ul>
        </section>
    </div>
</div>

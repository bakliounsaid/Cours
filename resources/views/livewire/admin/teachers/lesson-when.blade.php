<span class="block font-bold text-ink">{{ \App\Support\Format::dayDate($lesson->date, withYear: ! $lesson->date->isCurrentYear()) }}</span>
<span class="block text-xs text-subtle" dir="ltr">{{ \App\Support\Format::timeRange($lesson->starts_at, $lesson->ends_at) }}</span>

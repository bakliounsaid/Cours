<a href="{{ route('admin.teachers.show', $teacher) }}" wire:navigate class="group inline-flex items-center gap-3">
    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-olive-700 text-xs font-bold text-white">{{ $teacher->user->initials() }}</span>
    <span class="font-bold text-olive-700 group-hover:text-terra-700 group-hover:underline">{{ $teacher->user->name }}</span>
</a>

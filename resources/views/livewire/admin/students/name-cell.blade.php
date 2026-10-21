<a href="{{ route('admin.students.show', $student) }}" wire:navigate class="group inline-flex items-center gap-3">
    <span class="avatar size-9 text-xs">{{ $student->initials() }}</span>
    <span class="min-w-0">
        <span class="block font-bold text-olive-700 group-hover:text-terra-700 group-hover:underline">{{ $student->full_name }}</span>
        <span class="block truncate text-xs text-subtle" dir="ltr">{{ $student->parentPhoneDisplay() }}</span>
    </span>
</a>

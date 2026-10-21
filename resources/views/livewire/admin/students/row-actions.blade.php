<a href="{{ route('admin.students.show', $row) }}" wire:navigate title="{{ __('admin.students.open') }}"
   class="inline-flex size-9 items-center justify-center rounded-full text-muted transition hover:bg-sand-200 hover:text-olive-800">
    <x-icon name="eye" class="size-[18px]" /><span class="sr-only">{{ __('admin.students.open') }}</span>
</a>

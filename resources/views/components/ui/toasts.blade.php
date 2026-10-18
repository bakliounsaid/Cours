{{--
    Notifications « toast ».
    Depuis Livewire : $this->dispatch('toast', type: 'success', message: __('...'));
    Depuis une redirection : session()->flash('toast', ['type' => 'success', 'message' => '...']);
--}}
<div x-data="{
        toasts: [],
        add(detail) {
            const toast = { id: Date.now() + Math.random(), type: detail.type ?? 'success', message: detail.message };
            this.toasts.push(toast);
            setTimeout(() => this.remove(toast.id), detail.duration ?? 4500);
        },
        remove(id) { this.toasts = this.toasts.filter(t => t.id !== id) },
     }"
     x-init="@if (session('toast')) add(@js(session('toast'))) @endif"
     x-on:toast.window="add($event.detail)"
     class="pointer-events-none fixed inset-x-4 bottom-4 z-[70] flex flex-col items-center gap-2 sm:inset-x-auto sm:end-6 sm:bottom-6 sm:items-end"
     aria-live="polite">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-end="opacity-0"
             class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl bg-white p-4 shadow-lift ring-1 ring-olive-100">
            <span class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full"
                  :class="{
                    'bg-emerald-100 text-emerald-600': toast.type === 'success',
                    'bg-red-100 text-red-600': toast.type === 'error',
                    'bg-honey-100 text-honey-700': toast.type === 'warning',
                    'bg-olive-50 text-muted': toast.type === 'info',
                  }">
                <template x-if="toast.type === 'success'"><x-icon name="check" class="size-4" /></template>
                <template x-if="toast.type === 'error'"><x-icon name="x" class="size-4" /></template>
                <template x-if="toast.type === 'warning'"><x-icon name="warning" class="size-4" /></template>
                <template x-if="toast.type === 'info'"><x-icon name="info" class="size-4" /></template>
            </span>
            <p class="flex-1 text-sm font-semibold text-ink" x-text="toast.message"></p>
            <button type="button" x-on:click="remove(toast.id)" class="text-subtle hover:text-muted">
                <x-icon name="x" class="size-4" />
                <span class="sr-only">{{ __('app.close') }}</span>
            </button>
        </div>
    </template>
</div>

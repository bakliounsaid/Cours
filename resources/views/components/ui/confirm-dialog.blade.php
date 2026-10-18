{{--
    Modale de confirmation globale (une seule par page, dans les layouts).

    Depuis Alpine :
      $dispatch('confirm', { title: '…', message: '…', danger: true, onConfirm: () => $wire.delete(12) })

    Depuis Livewire (côté serveur) :
      $this->dispatch('confirm', title: '…', message: '…', component: $this->getId(), method: 'cancelLesson', params: [12]);
--}}
<div x-data="{
        open: false,
        title: '',
        message: '',
        confirmLabel: '',
        danger: true,
        callback: null,
        show(detail) {
            this.title = detail.title ?? @js(__('ui.delete_title'));
            this.message = detail.message ?? @js(__('ui.delete_message'));
            this.confirmLabel = detail.confirmLabel ?? @js(__('ui.confirm'));
            this.danger = detail.danger ?? true;
            this.callback = detail.onConfirm ?? (detail.component
                ? () => Livewire.find(detail.component).call(detail.method, ...(detail.params ?? []))
                : null);
            this.open = true;
        },
        confirm() {
            if (this.callback) this.callback();
            this.open = false;
        },
     }"
     x-on:confirm.window="show($event.detail)"
     x-show="open"
     x-on:keydown.escape.window="open = false"
     x-cloak
     class="fixed inset-0 z-[65] flex items-end justify-center p-4 sm:items-center"
     role="alertdialog"
     aria-modal="true">
    <div x-show="open" x-transition.opacity x-on:click="open = false" class="absolute inset-0 bg-olive-900/40 backdrop-blur-sm"></div>

    <div x-show="open"
         x-trap.inert.noscroll="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="relative w-full max-w-md rounded-3xl bg-white p-6 shadow-lift">
        <div class="flex gap-4">
            <span class="flex size-11 shrink-0 items-center justify-center rounded-full"
                  :class="danger ? 'bg-red-50 text-red-600' : 'bg-honey-100 text-honey-700'">
                <x-icon name="warning" class="size-6" />
            </span>
            <div>
                <h2 class="text-lg font-bold" x-text="title"></h2>
                <p class="mt-1 text-sm text-muted" x-text="message"></p>
            </div>
        </div>
        <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn-ghost" x-on:click="open = false">{{ __('ui.cancel') }}</button>
            <button type="button" x-on:click="confirm()" :class="danger ? 'btn-danger' : 'btn-primary'" x-text="confirmLabel"></button>
        </div>
    </div>
</div>

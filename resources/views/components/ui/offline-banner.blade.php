{{-- Bandeau affiché quand la connexion est perdue --}}
<div x-data="{ online: navigator.onLine }"
     x-on:online.window="online = true"
     x-on:offline.window="online = false"
     x-show="! online"
     x-cloak
     x-transition.opacity
     role="status"
     class="fixed inset-x-0 top-0 z-[60] flex items-center justify-center gap-2 bg-olive-800 px-4 py-2.5 text-sm font-semibold text-white shadow-lift">
    <x-icon name="signal-slash" class="size-5 text-honey-400" />
    <span>{{ __('app.offline') }}</span>
</div>

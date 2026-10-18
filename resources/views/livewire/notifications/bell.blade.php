<div x-data="{ open: false }" x-on:click.outside="open = false" class="relative">
    <button type="button" x-on:click="open = ! open" :aria-expanded="open"
            class="relative flex size-10 items-center justify-center rounded-full bg-white text-muted ring-1 ring-olive-100 transition hover:bg-terra-50 hover:text-terra-700">
        <x-icon name="bell" />
        @if ($unreadCount > 0)
            <span class="absolute -end-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-terra-600 px-1 text-[11px] font-extrabold text-white ring-2 ring-sand-100">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
        <span class="sr-only">{{ __('admin.notifications') }}</span>
    </button>

    <div x-show="open" x-transition.origin.top x-cloak
         class="fixed inset-x-3 top-16 z-50 rounded-2xl bg-white shadow-lift ring-1 ring-olive-100 sm:absolute sm:inset-x-auto sm:end-0 sm:top-auto sm:mt-2 sm:w-96">
        <div class="flex items-center justify-between border-b border-olive-50 px-4 py-3">
            <p class="font-bold text-ink">{{ __('admin.notifications') }}</p>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="text-xs font-bold text-terra-700 hover:underline">{{ __('admin.mark_all_read') }}</button>
            @endif
        </div>

        <ul class="max-h-[60dvh] divide-y divide-olive-50 overflow-y-auto">
            @forelse ($notifications as $notification)
                <li wire:key="notification-{{ $notification->id }}">
                    <button type="button" wire:click="open('{{ $notification->id }}')"
                            @class(['flex w-full items-start gap-3 px-4 py-3 text-start transition hover:bg-sand-50', 'bg-terra-50/40' => $notification->unread()])>
                        <span @class([
                            'mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full',
                            'bg-terra-100 text-terra-700' => ($notification->data['tone'] ?? '') === 'coral',
                            'bg-honey-100 text-honey-700' => ($notification->data['tone'] ?? '') === 'sun',
                            'bg-red-100 text-red-600' => ($notification->data['tone'] ?? '') === 'red',
                            'bg-olive-50 text-muted' => ! in_array($notification->data['tone'] ?? '', ['coral', 'sun', 'red'], true),
                        ])>
                            <x-icon :name="$notification->data['icon'] ?? 'bell'" class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span @class(['block text-sm text-olive-700', 'font-bold' => $notification->unread()])>{{ \App\Notifications\Concerns\InAppMessage::text($notification) }}</span>
                            <span class="mt-0.5 block text-xs text-subtle">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @if ($notification->unread())
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-terra-600"></span>
                        @endif
                    </button>
                </li>
            @empty
                <li class="px-4 py-10 text-center text-sm text-subtle">{{ __('admin.no_notifications') }}</li>
            @endforelse
        </ul>
    </div>
</div>

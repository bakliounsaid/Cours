<div>
    <h1 class="text-2xl font-extrabold">{{ __('auth.forgot_title') }}</h1>
    <p class="mt-1 text-sm text-muted">{{ __('auth.forgot_subtitle') }}</p>

    @if ($status)
        <div class="mt-5 flex items-start gap-2 rounded-2xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-700" role="status">
            <x-icon name="check-circle" /> {{ $status }}
        </div>
    @endif

    <form wire:submit="sendResetLink" class="mt-6 space-y-4">
        <x-ui.input wire:model="email" type="email" :label="__('auth.email')" autocomplete="username" required dir="ltr" />

        <button type="submit" class="btn-primary w-full py-3 text-base" wire:loading.attr="disabled">{{ __('auth.send_link') }}</button>
    </form>

    <a href="{{ route('login') }}" wire:navigate class="mt-5 inline-block text-sm font-semibold text-muted hover:text-terra-700">{{ __('auth.back_to_login') }}</a>
</div>

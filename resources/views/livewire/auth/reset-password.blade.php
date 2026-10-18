<div>
    <h1 class="text-2xl font-extrabold">{{ __('auth.reset_title') }}</h1>

    <form wire:submit="resetPassword" class="mt-6 space-y-4">
        <x-ui.input wire:model="email" type="email" :label="__('auth.email')" autocomplete="username" required dir="ltr" />
        <x-ui.input wire:model="password" type="password" :label="__('auth.new_password')" autocomplete="new-password" required dir="ltr" />
        <x-ui.input wire:model="password_confirmation" type="password" :label="__('auth.confirm_password')" autocomplete="new-password" required dir="ltr" />

        <button type="submit" class="btn-primary w-full py-3 text-base" wire:loading.attr="disabled">{{ __('auth.reset_submit') }}</button>
    </form>
</div>

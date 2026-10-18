<div>
    <h1 class="text-2xl font-extrabold">{{ $teacherSpace ? __('auth.teacher_login_title') : __('auth.login_title') }}</h1>
    <p class="mt-1 text-sm text-muted">{{ $teacherSpace ? __('auth.teacher_login_subtitle') : __('auth.login_subtitle') }}</p>

    <form wire:submit="login" class="mt-6 space-y-4">
        <x-ui.input wire:model="form.email" type="email" :label="__('auth.email')" autocomplete="username" autofocus required dir="ltr" />
        <x-ui.input wire:model="form.password" type="password" :label="__('auth.password_label')" autocomplete="current-password" required dir="ltr" />

        <div class="flex items-center justify-between gap-4">
            <label class="flex items-center gap-2 text-sm font-semibold text-muted">
                <input type="checkbox" wire:model="form.remember" class="size-4 rounded accent-terra-500">
                {{ __('auth.remember') }}
            </label>
            <a href="{{ route('password.request') }}" wire:navigate class="text-sm font-semibold text-terra-700 hover:underline">{{ __('auth.forgot') }}</a>
        </div>

        <button type="submit" class="btn-primary w-full py-3 text-base" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="login">{{ __('auth.submit') }}</span>
            <span wire:loading wire:target="login">{{ __('ui.loading') }}</span>
        </button>
    </form>
</div>

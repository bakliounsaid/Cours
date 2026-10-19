<?php

namespace App\Providers;

use App\Enums\Locale;
use App\Http\Middleware\EnsureUserHasRole;
use App\Settings\Settings;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Events\LocaleUpdated;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(Settings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->syncDateLocale();

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Les requêtes Livewire (/livewire/update) repassent par la vérification du rôle.
        Livewire::addPersistentMiddleware([EnsureUserHasRole::class]);

        ResetPassword::toMailUsing(fn (object $notifiable, string $token) => (new MailMessage)
            ->subject(__('auth.reset_mail_subject'))
            ->greeting(__('auth.greeting', ['name' => $notifiable->name]))
            ->line(__('auth.reset_mail_line'))
            ->action(__('auth.reset_mail_action'), route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line(__('auth.reset_mail_expire', ['count' => config('auth.passwords.users.expire')]))
            ->line(__('auth.reset_mail_ignore'))
            ->salutation(__('auth.signature')));
    }

    /**
     * Les dates (Carbon) suivent toujours la langue de l'application,
     * y compris dans les notifications envoyées en file d'attente.
     */
    private function syncDateLocale(): void
    {
        $apply = fn (string $locale) => Carbon::setLocale(Locale::tryFrom($locale)?->carbonLocale() ?? $locale);

        $apply(app()->getLocale());

        Event::listen(LocaleUpdated::class, fn (LocaleUpdated $event) => $apply($event->locale));
    }
}

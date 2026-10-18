<?php

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Cloche de notifications (mise à jour à chaque chargement de page).
 */
class Bell extends Component
{
    public function open(string $id): void
    {
        $notification = $this->findNotification($id);
        $notification->markAsRead();

        if ($url = $notification->data['url'] ?? null) {
            $this->redirect($url, navigate: true);
        }
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    private function findNotification(string $id): DatabaseNotification
    {
        return Auth::user()->notifications()->whereKey($id)->firstOrFail();
    }

    public function render(): View
    {
        $user = Auth::user();

        return view('livewire.notifications.bell', [
            'unreadCount' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->latest()->limit(8)->get(),
        ]);
    }
}

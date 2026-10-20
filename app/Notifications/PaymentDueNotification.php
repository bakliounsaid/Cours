<?php

namespace App\Notifications;

use App\Enums\PaymentStatus;
use App\Models\Student;
use App\Notifications\Concerns\InAppMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerte de paiement pour l'enseignant : un élève a atteint son cycle de séances
 * (ou le dépasse). Visible dans la cloche au prochain chargement de page.
 */
class PaymentDueNotification extends Notification
{
    public function __construct(
        private readonly Student $student,
        private readonly PaymentStatus $status,
        private readonly int $unpaid,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $overdue = $this->status === PaymentStatus::Overdue;

        return InAppMessage::make(
            $overdue ? 'notifications.payment_overdue' : 'notifications.payment_due',
            ['name' => $this->student->full_name, 'count' => $this->unpaid],
            route('teacher.students', ['suivi' => 'payment']),
            'banknotes',
            $overdue ? 'red' : 'coral',
        );
    }
}

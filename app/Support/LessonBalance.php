<?php

namespace App\Support;

use App\Enums\PaymentStatus;

/**
 * Compteur de séances d'un élève chez un enseignant.
 *
 * - attended : séances suivies (comptées selon le réglage des absences)
 * - covered : séances déjà payées (somme des cycles réglés)
 * - unpaid : séances suivies non couvertes par un paiement
 */
final readonly class LessonBalance
{
    public function __construct(
        public int $attended,
        public int $covered,
        public int $cycle,
        public bool $preReminder,
    ) {}

    public function unpaid(): int
    {
        return max(0, $this->attended - $this->covered);
    }

    /** Séances encore payées d'avance (paiement anticipé). */
    public function prepaid(): int
    {
        return max(0, $this->covered - $this->attended);
    }

    /**
     * À jour → bientôt (avant-dernière séance du cycle) → à régler (cycle atteint)
     * → en retard (au moins une séance de plus que le cycle sans paiement).
     */
    public function status(): PaymentStatus
    {
        $unpaid = $this->unpaid();

        return match (true) {
            $unpaid > $this->cycle => PaymentStatus::Overdue,
            $unpaid >= $this->cycle => PaymentStatus::Due,
            $this->preReminder && $unpaid === $this->cycle - 1 => PaymentStatus::ReminderSoon,
            default => PaymentStatus::UpToDate,
        };
    }

    /** Position dans le cycle en cours : « 3 / 4 ». */
    public function progressLabel(): string
    {
        return $this->unpaid().' / '.$this->cycle;
    }

    /** Remplissage de la jauge du cycle, de 0 à 100. */
    public function progressPercent(): int
    {
        return $this->cycle > 0 ? (int) min(100, round($this->unpaid() / $this->cycle * 100)) : 0;
    }

    /** Message à afficher (null quand l'élève est à jour). */
    public function alert(): ?string
    {
        return match ($this->status()) {
            PaymentStatus::UpToDate => null,
            PaymentStatus::ReminderSoon => __('teacher.payments.alert_soon'),
            PaymentStatus::Due => __('teacher.payments.alert_due', ['count' => $this->unpaid()]),
            PaymentStatus::Overdue => __('teacher.payments.alert_overdue', ['count' => $this->unpaid()]),
        };
    }
}

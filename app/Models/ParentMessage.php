<?php

namespace App\Models;

use App\Enums\Locale;
use App\Enums\MessageChannel;
use App\Enums\ParentMessageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Historique des SMS / WhatsApp envoyés aux parents.
 */
class ParentMessage extends Model
{
    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'student_id',
        'lesson_id',
        'type',
        'channel',
        'locale',
        'phone',
        'body',
        'status',
        'error',
        'sent_by',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ParentMessageType::class,
            'channel' => MessageChannel::class,
            'locale' => Locale::class,
            'sent_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}

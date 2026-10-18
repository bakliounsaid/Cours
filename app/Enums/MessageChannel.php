<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum MessageChannel: string
{
    use HasLabel;

    case Sms = 'sms';
    case Whatsapp = 'whatsapp';
}

<?php

namespace App\Enums;

use App\Enums\Concerns\HasLabel;

enum PaymentMethod: string
{
    use HasLabel;

    case Cash = 'cash';
    case Transfer = 'transfer';
    case Other = 'other';
}

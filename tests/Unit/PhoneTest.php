<?php

use App\Support\Phone;

it('normalise les numéros algériens', function (string $input, ?string $expected) {
    expect(Phone::normalize($input))->toBe($expected);
})->with([
    ['0555 12 34 56', '0555123456'],
    ['+213 555 12 34 56', '0555123456'],
    ['00213661234567', '0661234567'],
    ['021 23 45 67', '021234567'],
    ['0123', null],
    ['0855123456', null],
]);

it('formate pour l’affichage et WhatsApp', function () {
    expect(Phone::format('0555123456'))->toBe('0555 12 34 56')
        ->and(Phone::international('0555 12 34 56'))->toBe('213555123456')
        ->and(Phone::whatsappUrl('0555123456'))->toBe('https://wa.me/213555123456');
});

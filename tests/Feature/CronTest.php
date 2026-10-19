<?php

it('refuse l’appel planifié sans le secret Vercel Cron', function () {
    config(['educoeur.cron_secret' => 'un-secret']);

    $this->get(route('cron.lessons'))->assertForbidden();
    $this->get(route('cron.lessons'), ['Authorization' => 'Bearer mauvais'])->assertForbidden();
});

it('refuse tout appel tant qu’aucun secret n’est configuré', function () {
    config(['educoeur.cron_secret' => null]);

    $this->get(route('cron.lessons'), ['Authorization' => 'Bearer '])->assertForbidden();
});

it('génère les séances quand Vercel Cron présente le bon secret', function () {
    config(['educoeur.cron_secret' => 'un-secret']);

    $this->get(route('cron.lessons'), ['Authorization' => 'Bearer un-secret'])
        ->assertOk()
        ->assertJson(['ok' => true]);
});

<?php

/**
 * Application installable (PWA) : manifeste, icônes, service worker et page hors connexion.
 */
it('fournit un manifeste valide dont toutes les icônes existent', function () {
    $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

    expect($manifest['display'])->toBe('standalone')
        ->and($manifest['start_url'])->toStartWith('/connexion')
        ->and(collect($manifest['icons'])->pluck('sizes')->all())->toContain('192x192', '512x512')
        ->and(collect($manifest['icons'])->pluck('purpose')->all())->toContain('maskable');

    foreach ([...$manifest['icons'], ['src' => '/images/pwa/apple-touch-icon.png']] as $icon) {
        expect(public_path(ltrim($icon['src'], '/')))->toBeFile();
    }
});

it('fournit le service worker et la page hors connexion', function () {
    expect(public_path('sw.js'))->toBeFile()
        ->and(file_get_contents(public_path('sw.js')))->toContain('/offline.html')
        ->and(public_path('offline.html'))->toBeFile();
});

it('relie le manifeste et l’icône Apple dans toutes les pages', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('rel="manifest" href="'.asset('manifest.webmanifest').'"', false)
        ->assertSee(asset('images/pwa/apple-touch-icon.png'), false);
})->with(['/', '/connexion', '/enseignant/login']);

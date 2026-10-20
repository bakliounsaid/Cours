<?php

namespace App\Support;

/**
 * Photos de l'école (public/images/histoire), en WebP avec un JPEG de secours.
 * La première est la nouvelle salle fraîchement repeinte.
 */
class SchoolGallery
{
    private const PHOTOS = [
        // [nom du fichier, largeur, hauteur]
        ['nouvelle-salle', 1050, 1400],
        ['cours-arabe-tableau', 960, 720],
        ['premiers-mots', 1045, 1400],
        ['travail-en-groupe', 960, 720],
        ['au-tableau', 960, 720],
        ['concentration', 960, 635],
    ];

    /**
     * @return list<array{webp: string, jpg: string, width: int, height: int, caption: string}>
     */
    public static function photos(): array
    {
        return array_map(fn (array $photo) => [
            'webp' => asset("images/histoire/{$photo[0]}.webp"),
            'jpg' => asset("images/histoire/{$photo[0]}.jpg"),
            'width' => $photo[1],
            'height' => $photo[2],
            'caption' => __('home.gallery.captions.'.str_replace('-', '_', $photo[0])),
        ], self::PHOTOS);
    }
}

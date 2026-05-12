<?php

namespace App\Service;

use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public function __construct(
        // BuilderInterface injecté automatiquement par endroid/qr-code-bundle
        private readonly BuilderInterface $builder
    ) {}

    /**
     * Génère un QR code PNG pour l'URL donnée et retourne le contenu binaire.
     *
     * @param string $url  L'URL à encoder (lien meeting)
     * @param int    $size Taille en pixels (défaut : 300)
     */
    public function generatePng(string $url, int $size = 300): string
    {
        $result = $this->builder
            ->writer(new PngWriter())
            ->data($url)
            ->size($size)
            ->margin(12)
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            // Fond sombre pour matcher le thème dark du projet
            ->backgroundColor(new Color(13, 29, 48))
            // Premier plan cyan (--accent du thème)
            ->foregroundColor(new Color(79, 195, 247))
            ->build();

        return $result->getString();
    }

    /**
     * Génère un QR code encodé en base64, utilisable dans un <img src>.
     */
    public function generateBase64(string $url, int $size = 300): string
    {
        return 'data:image/png;base64,' . base64_encode($this->generatePng($url, $size));
    }
}

<?php

namespace App\Controller;

use App\Service\QrCodeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/qrcode')]
class QrCodeController extends AbstractController
{
    #[Route('/generate', name: 'app_qrcode_generate', methods: ['GET'])]
    public function generate(QrCodeService $qrCodeService): Response
    {
        // Example: generate a QR code for the homepage
        $url = $this->generateUrl('app_home', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $qrContent = $qrCodeService->generatePng($url);

        return new Response($qrContent, 200, ['Content-Type' => 'image/png']);
    }
}

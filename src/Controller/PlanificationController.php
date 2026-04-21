<?php

namespace App\Controller;

use App\Entity\Planification;
use App\Entity\User;
use App\Form\PlanificationType;
use App\Repository\PlanificationRepository;
use App\Service\ICalService;
use App\Service\PushNotificationService;
use App\Service\QrCodeService;
use App\Service\WeatherService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Service\PdfService;
use Twig\Environment;

#[Route('/planification')]
class PlanificationController extends AbstractController
{
    #[Route('', name: 'planification_index', methods: ['GET'])]
    public function index(PlanificationRepository $repo): Response
    {
        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $planifications = $isAdmin
            ? $repo->findAll()
            : ($currentUser instanceof User ? $repo->findByUser($currentUser) : []);

        $total = count($planifications);
        $actifs = $isAdmin ? $repo->countActifs() : 0;
        $nouveaux = $isAdmin ? $repo->countNouveaux(7) : 0;

        return $this->render('planification/index.html.twig', [
            'planifications' => $planifications,
            'total' => $total,
            'actifs' => $actifs,
            'nouveaux' => $nouveaux,
        ]);
    }

    #[Route('/export-pdf', name: 'planification_export_pdf', methods: ['GET'])]
    public function exportPdf(PlanificationRepository $repo, PdfService $pdf, Environment $twig): Response
    {
        $planifications = $repo->findAll();

        $html = $twig->render('planification/export_pdf.html.twig', [
            'planifications' => $planifications,
        ]);

        $content = $pdf->generateFromHtml($html, 'landscape');

        $filename = 'Planifications_Nexus_' . date('Y-m-d_H-i') . '.pdf';

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    #[Route('/events', name: 'planification_events', methods: ['GET'])]
    public function events(PlanificationRepository $repo): JsonResponse
    {
        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        $planifications = $isAdmin
            ? $repo->findAll()
            : ($currentUser instanceof User ? $repo->findByUser($currentUser) : []);
        $events = [];

        $colorMap = [
            'entretien' => '#4e73df',
            'reunion' => '#1cc88a',
            'formation' => '#f6c23e',
            'autre' => '#e74a3b',
        ];

        foreach ($planifications as $p) {
            $dateStr = $p->getDate()?->format('Y-m-d') ?? date('Y-m-d');
            $start = $dateStr . 'T' . ($p->getHeureDebut()?->format('H:i:s') ?? '00:00:00');
            $end = $dateStr . 'T' . ($p->getHeureFin()?->format('H:i:s') ?? '01:00:00');
            $color = $colorMap[$p->getTypeEvent()] ?? '#6c757d';

            $events[] = [
                'id' => $p->getIdEvent(),
                'title' => ucfirst($p->getTypeEvent()) . ($p->getDescription() ? ' - ' . mb_substr($p->getDescription(), 0, 30) : ''),
                'start' => $start,
                'end' => $end,
                'color' => $color,
                'extendedProps' => [
                    'statut' => $p->getStatut(),
                    'mode' => $p->getMode(),
                    'url' => $this->generateUrl('planification_show', ['id' => $p->getIdEvent()]),
                    'icalUrl' => $this->generateUrl('planification_export_ical', ['id' => $p->getIdEvent()]),
                ],
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/new', name: 'planification_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, PushNotificationService $push): Response
    {
        $planification = new Planification();

        $dateParam = $request->query->get('date');
        if ($dateParam) {
            try {
                $planification->setDate(new \DateTime($dateParam));
            } catch (\Exception) {
            }
        }

        $form = $this->createForm(PlanificationType::class, $planification);
        $form->handleRequest($request);

        $isModal = $request->query->get('modal') === '1';

        if ($form->isSubmitted() && $form->isValid()) {
            // Lier automatiquement le candidat si aucun responsable n'est défini
            if ($planification->getUser() === null) {
                $currentUser = $this->getUser();
                if ($currentUser instanceof User) {
                    $planification->setUser($currentUser);
                }
            }

            $em->persist($planification);
            $em->flush();

            $dateLabel = $planification->getDate()?->format('d/m/Y') ?? '';
            $push->sendToAll(
                '📅 Nouvelle planification',
                sprintf('📅 Nouvelle planification : %s le %s', ucfirst((string) $planification->getTypeEvent()), $dateLabel),
                $this->generateUrl('planification_show', ['id' => $planification->getIdEvent()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL)
            );

            if ($isModal) {
                return new \Symfony\Component\HttpFoundation\Response(
                    '<script>window.parent.postMessage("planification_saved", "*");</script>'
                );
            }

            $this->addFlash('success', 'Planification créée avec succès.');
            return $this->redirectToRoute('planification_index');
        } elseif ($form->isSubmitted()) {
            foreach ($form->getErrors(true) as $error) {
                error_log("Form Error: " . $error->getMessage());
                error_log("At path: " . $error->getOrigin()->getName());
            }
        }


        $template = $isModal
            ? 'planification/new_modal.html.twig'
            : 'planification/new.html.twig';

        return $this->render($template, [
            'form' => $form,
            'planification' => $planification,
        ]);
    }

    #[Route('/{id}/qrcode', name: 'planification_qrcode', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function qrCode(Planification $planification, QrCodeService $qrCodeService): Response
    {
        $url = $planification->getLienMeeting();

        if (!$url) {
            throw $this->createNotFoundException('Aucun lien meeting disponible pour cet événement.');
        }

        $png = $qrCodeService->generatePng($url, 300);

        return new Response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    #[Route('/{id}/export-ical', name: 'planification_export_ical', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function exportIcal(Planification $planification, ICalService $icalService): Response
    {
        try {
            $content = $icalService->generateFromPlanification($planification);

            return new Response($content, 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => sprintf(
                    'attachment; filename="planification-%d.ics"',
                    $planification->getIdEvent()
                ),
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Erreur lors de la génération du fichier iCal : ' . $e->getMessage());
            return $this->redirectToRoute('planification_show', ['id' => $planification->getIdEvent()]);
        }
    }

    #[Route('/{id}', name: 'planification_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Planification $planification, WeatherService $weather): Response
    {
        $weatherData = null;
        if ($planification->getDate() !== null) {
            $weatherData = $weather->getWeatherForEvent(
                $planification->getDate(),
                $planification->getLocalisation(),
                $planification->getHeureDebut(),
            );
        }

        return $this->render('planification/show.html.twig', [
            'planification' => $planification,
            'weather' => $weatherData,
        ]);
    }

    #[Route('/{id}/edit', name: 'planification_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Planification $planification, EntityManagerInterface $em, PushNotificationService $push): Response
    {
        $form = $this->createForm(PlanificationType::class, $planification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newStatut = $planification->getStatut();
            $em->flush();

            if ($newStatut === 'confirmé') {
                $push->sendToAll(
                    '✅ Entretien confirmé',
                    '✅ Votre entretien est confirmé !'
                );
            } else {
                $push->sendToAll(
                    '✏️ Planification modifiée',
                    sprintf('✏️ Planification modifiée : %s', ucfirst((string) $planification->getTypeEvent()))
                );
            }

            $this->addFlash('success', 'Planification modifiée avec succès.');
            return $this->redirectToRoute('planification_index');
        }

        return $this->render('planification/edit.html.twig', [
            'form' => $form,
            'planification' => $planification,
        ]);
    }

    #[Route('/{id}/delete', name: 'planification_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Planification $planification, EntityManagerInterface $em, PushNotificationService $push): Response
    {
        if ($this->isCsrfTokenValid('delete' . $planification->getIdEvent(), $request->request->get('_token'))) {
            $em->remove($planification);
            $em->flush();
            $push->sendToAll('🗑️ Planification annulée', '🗑️ Planification annulée');
            $this->addFlash('success', 'Planification supprimée.');
        }

        return $this->redirectToRoute('planification_index');
    }
}

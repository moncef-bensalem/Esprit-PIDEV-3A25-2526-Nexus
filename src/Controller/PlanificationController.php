<?php

namespace App\Controller;

use App\Entity\Planification;
use App\Form\PlanificationType;
use App\Repository\PlanificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planification')]
class PlanificationController extends AbstractController
{
    #[Route('', name: 'planification_index', methods: ['GET'])]
    public function index(PlanificationRepository $repo): Response
    {
        $planifications = $repo->findAll();
        $total = count($planifications);
        $actifs = $repo->countActifs();
        $nouveaux = $repo->countNouveaux(7);

        return $this->render('planification/index.html.twig', [
            'planifications' => $planifications,
            'total' => $total,
            'actifs' => $actifs,
            'nouveaux' => $nouveaux,
        ]);
    }

    #[Route('/events', name: 'planification_events', methods: ['GET'])]
    public function events(PlanificationRepository $repo): JsonResponse
    {
        $planifications = $repo->findAll();
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
                ],
            ];
        }

        return new JsonResponse($events);
    }

    #[Route('/new', name: 'planification_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
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
            $em->persist($planification);
            $em->flush();

            if ($isModal) {
                // Signal the parent window to close modal and refresh calendar
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

    #[Route('/{id}', name: 'planification_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Planification $planification): Response
    {
        return $this->render('planification/show.html.twig', [
            'planification' => $planification,
        ]);
    }

    #[Route('/{id}/edit', name: 'planification_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Planification $planification, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PlanificationType::class, $planification);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Planification modifiée avec succès.');
            return $this->redirectToRoute('planification_index');
        }

        return $this->render('planification/edit.html.twig', [
            'form' => $form,
            'planification' => $planification,
        ]);
    }

    #[Route('/{id}/delete', name: 'planification_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Planification $planification, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $planification->getIdEvent(), $request->request->get('_token'))) {
            $em->remove($planification);
            $em->flush();
            $this->addFlash('success', 'Planification supprimée.');
        }

        return $this->redirectToRoute('planification_index');
    }
}

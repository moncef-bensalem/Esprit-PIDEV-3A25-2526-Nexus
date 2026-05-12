<?php

namespace App\Controller\Admin;

use App\Repository\AdminNotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/notifications')]
#[IsGranted(new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_RH')"))]
class AdminNotificationController extends AbstractController
{
    #[Route('/mark-all-read', name: 'admin_notif_mark_all_read', methods: ['POST'])]
    public function markAllRead(Request $request, AdminNotificationRepository $repo): Response
    {
        if (!$this->isCsrfTokenValid('admin_notif_mark_all_read', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $repo->markAllAsRead();

        $referer = (string) $request->headers->get('referer');
        return $referer ? $this->redirect($referer) : $this->redirectToRoute('admin_dashboard');
    }
}


<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserEvent;
use App\Repository\UserEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted(new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_RH')"))]
class UserStatsController extends AbstractController
{
    #[Route('/{id}/stats', name: 'admin_user_stats', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function stats(User $user, UserEventRepository $repo): Response
    {
        $now = new \DateTimeImmutable('now');

        $todayStart = $now->setTime(0, 0, 0);
        $tomorrowStart = $todayStart->modify('+1 day');

        $monthStart = $now->modify('first day of this month')->setTime(0, 0, 0);
        $nextMonthStart = $monthStart->modify('+1 month');

        $loginToday = $repo->countForUserInRange($user, UserEvent::TYPE_LOGIN_SUCCESS, $todayStart, $tomorrowStart);
        $loginMonth = $repo->countForUserInRange($user, UserEvent::TYPE_LOGIN_SUCCESS, $monthStart, $nextMonthStart);

        $pwdMonth = $repo->countForUserInRange($user, UserEvent::TYPE_PASSWORD_CHANGED, $monthStart, $nextMonthStart);

        // Série 14 jours (login)
        $from = $todayStart->modify('-13 days');
        $to = $tomorrowStart;
        $series = $repo->dailySeriesForUser($user, UserEvent::TYPE_LOGIN_SUCCESS, $from, $to);

        return $this->render('admin/user/stats.html.twig', [
            'user' => $user,
            'loginToday' => $loginToday,
            'loginMonth' => $loginMonth,
            'passwordChangesMonth' => $pwdMonth,
            'loginSeriesFrom' => $from,
            'loginSeriesTo' => $to,
            'loginSeries' => $series,
        ]);
    }
}


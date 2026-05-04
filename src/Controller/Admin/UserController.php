<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserEvent;
use App\Entity\AdminNotification;
use App\Service\AdminNotificationService;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted(new Expression("is_granted('ROLE_ADMIN') or is_granted('ROLE_RH')"))]
class UserController extends AbstractController
{
    #[Route('', name: 'admin_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $repository): Response
    {
        $query = $request->query->get('q');
        $role = $request->query->get('role');

        $users = $repository->searchUsers($query, $role);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
            'currentQuery' => $query,
            'currentRole' => $role,
        ]);
    }

    #[Route('/search', name: 'admin_user_search', methods: ['GET'])]
    public function search(Request $request, UserRepository $repository): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('admin_user_index', $request->query->all());
        }

        $query = $request->query->get('q');
        $role = $request->query->get('role');
        $users = $repository->searchUsers($query, $role);

        $response = $this->render('admin/user/_rows.html.twig', [
            'users' => $users,
        ]);

        // Evite de servir une réponse en cache pendant la frappe
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        return $response;
    }

    #[Route('/new', name: 'admin_user_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'User created.');

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_user_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_user_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        AdminNotificationService $adminNotificationService
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            if ($plainPassword !== '') {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                $entityManager->persist((new UserEvent($user, UserEvent::TYPE_PASSWORD_CHANGED))
                    ->setIp($request->getClientIp())
                    ->setUserAgent($request->headers->get('User-Agent')));

                $adminNotificationService->notify(
                    AdminNotification::TYPE_PASSWORD_CHANGED,
                    'Mot de passe modifié',
                    "Utilisateur: {$user->getEmail()}",
                    'warning'
                );
            }

            $entityManager->flush();
            $this->addFlash('success', 'User updated.');

            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_user_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'User deleted.');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function toggleActive(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_user_' . $user->getId(), (string) $request->request->get('_token'))) {
            $user->setIsActive(!$user->getIsActive());
            $entityManager->flush();

            $status = $user->getIsActive() ? 'activé' : 'désactivé';
            $this->addFlash('success', 'Le compte de ' . $user->getFirstName() . ' a été ' . $status . '.');
        }

        return $this->redirectToRoute('admin_user_index');
    }
}

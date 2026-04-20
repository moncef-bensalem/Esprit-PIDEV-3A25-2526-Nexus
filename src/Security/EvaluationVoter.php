<?php

namespace App\Security;

use App\Entity\Evaluation;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * VIEW : ROLE_ADMIN ou recruteur proprietaire.
 * EDIT : uniquement le recruteur proprietaire (RH et admin).
 * DELETE : ROLE_ADMIN ou recruteur proprietaire.
 */
class EvaluationVoter extends Voter
{
    public const VIEW = 'EVALUATION_VIEW';

    public const EDIT = 'EVALUATION_EDIT';

    public const DELETE = 'EVALUATION_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Evaluation
            && \in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        $evaluation = $subject;
        $owner = $this->isOwner($user, $evaluation);

        return match ($attribute) {
            self::VIEW => $this->isAdmin($user) || $owner,
            self::EDIT => $owner,
            self::DELETE => $this->isAdmin($user) || $owner,
            default => false,
        };
    }

    private function isAdmin(User $user): bool
    {
        return \in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    private function isOwner(User $user, Evaluation $evaluation): bool
    {
        $recruteur = $evaluation->getRecruteur();

        return $recruteur instanceof User && $recruteur->getId() === $user->getId();
    }
}

<?php

namespace App\Service;

use App\Entity\Review;

class ReviewManager
{
    public function validate(Review $review): bool
    {
        if ($review->getRating() === null) {
            throw new \InvalidArgumentException('La note est obligatoire.');
        }

        if ($review->getRating() < 1 || $review->getRating() > 5) {
            throw new \InvalidArgumentException('La note doit être entre 1 et 5.');
        }

        if (empty($review->getCommentaire())) {
            throw new \InvalidArgumentException('Le commentaire ne peut pas être vide.');
        }

        return true;
    }
}

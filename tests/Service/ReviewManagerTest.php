<?php

namespace App\Tests\Service;

use App\Entity\Review;
use App\Entity\User;
use App\Service\ReviewManager;
use PHPUnit\Framework\TestCase;

class ReviewManagerTest extends TestCase
{
    private function makeReview(): Review
    {
        return new Review(new User());
    }

    public function testValidReview(): void
    {
        $review = $this->makeReview();
        $review->setRating(4);
        $review->setCommentaire('Très bon entretien, candidat sérieux.');

        $manager = new ReviewManager();
        $this->assertTrue($manager->validate($review));
    }

    public function testReviewWithoutRating(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note est obligatoire.');

        $review = $this->makeReview();
        $review->setCommentaire('Bon candidat.');

        $manager = new ReviewManager();
        $manager->validate($review);
    }

    public function testReviewWithRatingBelowMin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5.');

        $review = $this->makeReview();
        $review->setRating(0);
        $review->setCommentaire('Commentaire valide.');

        $manager = new ReviewManager();
        $manager->validate($review);
    }

    public function testReviewWithRatingAboveMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La note doit être entre 1 et 5.');

        $review = $this->makeReview();
        $review->setRating(6);
        $review->setCommentaire('Commentaire valide.');

        $manager = new ReviewManager();
        $manager->validate($review);
    }

    public function testReviewWithEmptyCommentaire(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Le commentaire ne peut pas être vide.');

        $review = $this->makeReview();
        $review->setRating(3);
        $review->setCommentaire('');

        $manager = new ReviewManager();
        $manager->validate($review);
    }
}

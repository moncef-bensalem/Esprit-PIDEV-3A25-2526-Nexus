<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class NoHateSpeech extends Constraint
{
    public string $message = 'Le texte contient un langage haineux et ne peut pas etre enregistre.';
}

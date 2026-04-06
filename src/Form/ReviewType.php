<?php

namespace App\Form;

use App\Entity\Review;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rating', IntegerType::class, [
                'label' => 'Note',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'display:none',
                    'min' => 1,
                    'max' => 5,
                ],
            ])
            ->add('commentaire', TextareaType::class, [
                'label' => 'Commentaire',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Votre commentaire...'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut de la planification',
                'required' => false,
                'placeholder' => '-- Choisir --',
                'choices' => [
                    'Terminé'           => 'DONE',
                    'Annulé'            => 'CANCELLED',
                    'Suivi nécessaire'  => 'NEEDS_FOLLOWUP',
                ],
                'attr' => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Review::class,
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }
}

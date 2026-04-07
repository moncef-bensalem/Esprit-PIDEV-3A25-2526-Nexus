<?php

namespace App\Form;

use App\Entity\Planification;
use App\Entity\Candidature;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typeEvent', ChoiceType::class, [
                'label' => 'Type d\'événement',
                'choices' => [
                    'Entretien' => 'entretien',
                    'Réunion' => 'reunion',
                    'Formation' => 'formation',
                    'Autre' => 'autre',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heureDebut', TimeType::class, [
                'label' => 'Heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('heureFin', TimeType::class, [
                'label' => 'Heure de fin',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('mode', ChoiceType::class, [
                'label' => 'Mode',
                'required' => false,
                'placeholder' => '-- Choisir --',
                'choices' => [
                    'Présentiel' => 'presentiel',
                    'En ligne' => 'en_ligne',
                    'Hybride' => 'hybride',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'required' => false,
                'placeholder' => '-- Choisir --',
                'choices' => [
                    'Confirmé' => 'confirmé',
                    'En attente' => 'en_attente',
                    'Annulé' => 'annulé',
                    'Terminé' => 'terminé',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ])
            ->add('lienMeeting', TextType::class, [
                'label' => 'Lien Meeting',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'https://...'],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('candidature', EntityType::class, [
                'label' => 'Candidature',
                'class' => Candidature::class,
                'choice_label' => function(Candidature $c) { return $c->getId_candidature(); },
                'required' => false,
                'placeholder' => '-- Aucune --',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('user', EntityType::class, [
                'label' => 'Responsable',
                'class' => User::class,
                'choice_label' => 'email',
                'required' => false,
                'placeholder' => '-- Aucun --',
                'attr' => ['class' => 'form-select'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planification::class,
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }
}

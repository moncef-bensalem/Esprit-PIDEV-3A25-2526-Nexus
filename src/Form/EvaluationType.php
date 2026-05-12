<?php

namespace App\Form;

use App\Entity\Evaluation;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaireGlobal', TextareaType::class, [
                'label' => 'Commentaire global',
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Saisir le commentaire global...',
                ],
            ])
            ->add('decisionPreliminaire', ChoiceType::class, [
                'label' => 'Décision préliminaire',
                'required' => true,
                'placeholder' => '-- Choisir --',
                'empty_data' => '',
                'choices' => [
                    'FAVORABLE' => 'FAVORABLE',
                    'DEFAVORABLE' => 'DEFAVORABLE',
                    'A_REVOIR' => 'A_REVOIR',
                ],
                'attr' => ['class' => 'form-select'],
            ])
            ->add('reviewDeadline', DateType::class, [
                'label' => 'Date limite de review',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('candidat', EntityType::class, [
                'label' => 'Candidat',
                'class' => User::class,
                'required' => true,
                'placeholder' => '-- Choisir un candidat --',
                'choices' => $options['candidates'],
                'choice_label' => static function (User $user): string {
                    return trim($user->getFirstName().' '.$user->getLastName());
                },
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
            'candidates' => [],
        ]);
        $resolver->setAllowedTypes('candidates', 'array');
    }
}

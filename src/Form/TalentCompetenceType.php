<?php

namespace App\Form;

use App\Entity\Competence;
use App\Entity\Talent;
use App\Entity\TalentCompetence;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TalentCompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveau_maitrise')
            ->add('annees_pratique')
            ->add('date_acquisition')
            ->add('talent', EntityType::class, [
                'class' => Talent::class,
                'choice_label' => 'id',
            ])
            ->add('competence', EntityType::class, [
                'class' => Competence::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TalentCompetence::class,
        ]);
    }
}

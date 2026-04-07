<?php

namespace App\Form;

use App\Entity\Talent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\DateType;

class TalentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('email')
            ->add('telephone')
            ->add('poste')
            ->add('departement')
            ->add('date_embauche', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('annees_experience')
            ->add('niveau_etudes')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Talent::class,
        ]);
    }
}

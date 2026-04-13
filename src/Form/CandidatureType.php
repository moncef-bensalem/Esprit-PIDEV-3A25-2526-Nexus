<?php

namespace App\Form;

use App\Entity\Candidat;
use App\Entity\Candidature;
use App\Entity\OffreEmploi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('candidat', EntityType::class, [
                'class' => Candidat::class,
                'choice_label' => 'nom_complet',
                'label' => 'Candidat'
            ])
            ->add('offre_emploi', EntityType::class, [
                'class' => OffreEmploi::class,
                'choice_label' => 'titre_poste',
                'label' => 'Offre d\'emploi'
            ])
            ->add('etat_avancement', ChoiceType::class, [
                'choices' => [
                    'Reçu' => 'RECU',
                    'En entretien' => 'EN_ENTRETIEN',
                    'Offre faite' => 'OFFRE_FAITE',
                    'Rejeté' => 'REJETE'
                ],
                'label' => 'État d\'avancement'
            ])
            ->add('score_matching', NumberType::class, [
                'required' => false, 
                'label' => 'Score AI (%)',
                'scale' => 2,
                'disabled' => true
            ])
            ->add('source_candidature', TextType::class, ['required' => false, 'label' => 'Source'])
            ->add('notes', TextareaType::class, [
                'required' => false, 
                'label' => 'Notes'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
            'attr' => [
                'novalidate' => 'novalidate', // disable html5 validation
            ]
        ]);
    }
}

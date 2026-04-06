<?php

namespace App\Form;

use App\Entity\OffreEmploi;
use App\Entity\Departement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OffreEmploiType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre_poste', TextType::class, [
                'label' => 'Titre du poste',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Le titre est requis.']),
                    new \Symfony\Component\Validator\Constraints\Length(['min' => 5, 'max' => 255, 'minMessage' => 'Le titre doit faire au moins 5 caractères.'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description', 
                'required' => true,
                'constraints' => [new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'La description est requise.'])]
            ])
            ->add('departementRel', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => 'libelle',
                'label' => 'Département',
                'constraints' => [new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Veuillez sélectionner un département.'])]
            ])
            ->add('date_cloture', DateType::class, [
                'widget' => 'single_text',
                'required' => true,
                'label' => 'Date de clôture',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'La date de clôture est requise.']),
                    new \Symfony\Component\Validator\Constraints\GreaterThanOrEqual(['value' => 'today', 'message' => 'La date de clôture ne peut pas être dans le passé.'])
                ]
            ])
            ->add('statut_offre', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => 'Brouillon',
                    'Publiée' => 'Publiée',
                    'Clôturée' => 'Clôturée'
                ],
                'label' => 'Statut'
            ])
            ->add('salaire_propose', NumberType::class, [
                'required' => true, 
                'label' => 'Salaire proposé',
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Veuillez saisir un salaire.']),
                    new \Symfony\Component\Validator\Constraints\PositiveOrZero(['message' => 'Le salaire ne peut pas être négatif.'])
                ]
            ])
            ->add('devise', ChoiceType::class, [
                'required' => true, 
                'label' => 'Devise',
                'choices' => [
                    'TND' => 'TND',
                    'EUR' => 'EUR',
                    'USD' => 'USD'
                ],
                'constraints' => [new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Veuillez sélectionner une devise.'])]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
            'attr' => [
                'novalidate' => 'novalidate', // disable html5 validation
            ]
        ]);
    }
}

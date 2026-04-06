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
            ->add('titre_poste', TextType::class, ['label' => 'Titre du poste'])
            ->add('description', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->add('departementRel', EntityType::class, [
                'class' => Departement::class,
                'choice_label' => 'libelle',
                'label' => 'Département'
            ])
            ->add('date_cloture', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date de clôture'
            ])
            ->add('statut_offre', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => 'BROUILLON',
                    'Publiée' => 'PUBLIEE',
                    'Clôturée' => 'CLOTUREE'
                ],
                'label' => 'Statut'
            ])
            ->add('salaire_propose', NumberType::class, ['required' => false, 'label' => 'Salaire proposé'])
            ->add('devise', TextType::class, ['required' => false, 'label' => 'Devise'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OffreEmploi::class,
        ]);
    }
}

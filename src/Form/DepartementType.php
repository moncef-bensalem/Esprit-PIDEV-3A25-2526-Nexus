<?php

namespace App\Form;

use App\Entity\Departement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DepartementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', TextType::class, [
                'label' => 'Libellé du Département',
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank(['message' => 'Le libellé ne peut pas être vide.']),
                    new \Symfony\Component\Validator\Constraints\Length(['min' => 2, 'max' => 255, 'minMessage' => 'Le libellé doit contenir au moins 2 caractères.']),
                    new \Symfony\Component\Validator\Constraints\Regex(['pattern' => '/^[A-Za-zÀ-ÿ0-9\s\-&]+$/', 'message' => 'Caractères non autorisés détectés.'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Departement::class,
            'attr' => [
                'novalidate' => 'novalidate', // disable html5 validation
            ]
        ]);
    }
}

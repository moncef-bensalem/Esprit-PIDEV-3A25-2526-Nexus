<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'L\'email est obligatoire.'), new Email(message: 'Veuillez saisir un email valide.')],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'Le prénom est obligatoire.'), new Length(min: 2, max: 50, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.')],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.'), new Length(min: 2, max: 50, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')],
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Candidate' => 'ROLE_CANDIDATE',
                ],
                'expanded' => false,
                'multiple' => true,
                'attr' => ['class' => 'form-select'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => !$isEdit,
                'empty_data' => '',
                'attr' => ['class' => 'form-control'],
                'help' => $isEdit ? 'Laisser vide pour garder le mot de passe actuel.' : null,
                'constraints' => $isEdit
                    ? [
                        new Length(min: 8, max: 255, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'),
                        new Regex(pattern: '/^(?=.*[A-Z])(?=.*\d).+$/', message: 'Le mot de passe doit contenir au moins une majuscule et un chiffre.'),
                    ]
                    : [
                        new NotBlank(message: 'Le mot de passe est obligatoire.'),
                        new Length(min: 8, max: 255, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.'),
                        new Regex(pattern: '/^(?=.*[A-Z])(?=.*\d).+$/', message: 'Le mot de passe doit contenir au moins une majuscule et un chiffre.'),
                    ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}

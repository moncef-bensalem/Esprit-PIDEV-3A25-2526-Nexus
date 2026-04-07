<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'L\'email est obligatoire.'), new Email(message: 'Veuillez saisir un email valide.')],
            ])
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'Le prénom est obligatoire.'), new Length(min: 2, max: 50, minMessage: 'Le prénom doit contenir au moins {{ limit }} caractères.')],
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'constraints' => [new NotBlank(message: 'Le nom est obligatoire.'), new Length(min: 2, max: 50, minMessage: 'Le nom doit contenir au moins {{ limit }} caractères.')],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Mot de passe', 'attr' => ['class' => 'form-control']],
                'second_options' => ['label' => 'Confirmer le mot de passe', 'attr' => ['class' => 'form-control']],
                'invalid_message' => 'Les mots de passe ne correspondent pas.',
                'constraints' => [
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
        ]);
    }
}

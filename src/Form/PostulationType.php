<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class PostulationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_complet', TextType::class, [
                'label' => 'Nom Complet',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre nom.']),
                    new Length(['min' => 3, 'max' => 200, 'minMessage' => 'Le nom doit faire au moins 3 caractères.']),
                    new Regex(['pattern' => '/^[a-zA-ZÀ-ÿ\s\-]+$/', 'message' => 'Le nom ne doit contenir que des lettres et des espaces.'])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez saisir votre email.']),
                    new Email([
                        'message' => 'L\'email {{ value }} n\'est pas un email valide.',
                        'mode' => 'strict'
                    ])
                ]
            ])
            ->add('cv', FileType::class, [
                'label' => 'Curriculum Vitae (PDF ou DOCX)',
                'mapped' => false, 
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez uploader obligatoirement votre CV pour postuler.']),
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un document PDF ou Word valide.',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate']
        ]);
    }
}

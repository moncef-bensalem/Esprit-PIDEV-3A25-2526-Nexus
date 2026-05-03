<?php

namespace App\Form;

use App\Entity\Candidat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CandidatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_complet', TextType::class, ['label' => 'Nom Complet'])
            ->add('email_contact', EmailType::class, ['label' => 'Email'])
            ->add('cv_file', FileType::class, [
                'label' => 'Télécharger CV (PDF/DOCX)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/msword'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document PDF ou Word valide',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
            'attr' => [
                'novalidate' => 'novalidate', // disable html5 validation
            ]
        ]);
    }
}

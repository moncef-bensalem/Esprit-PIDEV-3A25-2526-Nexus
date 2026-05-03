<?php

namespace App\Form;

use App\Entity\ScoreCompetence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ScoreCompetenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomCritere', TextType::class, [
                'label' => 'Nom critere',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Communication',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Le nom du critere est obligatoire.'),
                ],
            ])
            ->add('noteAttribuee', TextType::class, [
                'label' => 'Note attribuee (0 a 20)',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 14.5',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'La note attribuee est obligatoire.'),
                    new Assert\Callback(function (mixed $value, ExecutionContextInterface $context): void {
                        $raw = is_string($value) ? trim($value) : '';
                        if ($raw === '') {
                            return;
                        }

                        $normalized = str_replace(',', '.', $raw);
                        if (!is_numeric($normalized)) {
                            $context->buildViolation('La note doit etre un nombre entre 0 et 20.')
                                ->addViolation();
                            return;
                        }

                        $score = (float) $normalized;
                        if ($score < 0 || $score > 20) {
                            $context->buildViolation('La note doit etre comprise entre 0 et 20.')
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('appreciationSpecifique', TextareaType::class, [
                'label' => 'Appreciation specifique',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Optionnelle...',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScoreCompetence::class,
        ]);
    }
}

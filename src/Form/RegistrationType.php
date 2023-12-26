<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;



class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class,[
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 180,
                    'placeholder' => 'Email'
                ],
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 180
                    ])
                ]
            ])
            ->add('fullName',TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 50,
                    'placeholder' => 'Nom / Prénom'
                ],
                'label' => 'Nom /prenom',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50
                    ])
                ]
            ])
            ->add('pseudo', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 50,
                    'placeholder' => 'pseudo'
                ],
                'required' => false, // champ optionnel
                'label' => 'pseudo',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class, 
                'first_options' => [
                    'attr' => [
                        'class' => 'form-control',
                        'minlength' => 8,
                        'maxlength' => 50,
                        'placeholder' => 'Mot de passe'
                    ],
                    'label' => 'Mot de passe',
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                    ],
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'form-control',
                        'minlength' => 8,
                        'maxlength' => 50,
                        'placeholder' => 'Confirmation du mot de passe'
                    ],
                    'label' => 'confirmation du mot de passe',
                    'label_attr' => [
                        'class' => 'form-label mt-4'
                ],
                'invalid_message' => 'Les mots de passe ne correspondent pas',
            ]])
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire',
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

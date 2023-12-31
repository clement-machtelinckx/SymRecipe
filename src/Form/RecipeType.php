<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RecipeType extends AbstractType
{
    private $security;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
        ->add('name', TextType::class, [
            'attr' => [
                'class' => 'form-control',
                'minlength' => 2,
                'maxlength' => 50,
            ], 
            'label' => 'Nom',
            'label_attr' => [
                'class' => 'form-label mt-4'
            ],
            'constraints' => [
                new Assert\Length(['min' => 2, 'max' => 50]),
                new Assert\NotBlank()
            ]
        ])
            ->add('time', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 1441,
                ], 
                'required' => false,
                'label' => 'Temps de préparation en minutes',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThanOrEqual(1440),
                    new Assert\NotBlank()
                ]
            ])
            ->add('nbPeople', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'max' => 50,
                ], 
                'required' => false,
                'label' => 'Nombre de personnes',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThanOrEqual(10),
                    new Assert\NotBlank()
                ]
            ])
            ->add('difficulty', RangeType::class, [
                'attr' => [
                    'class' => 'form-range',
                    'min' => 1,
                    'max' => 5,
                ], 
                'label' => 'Difficulté',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThanOrEqual(5),
                    new Assert\NotBlank()
                ]
            ])
            ->add('description', TextAreaType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 500,
                ], 

                'label' => 'Description',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Length(['min' => 2, 'max' => 500]),
                    new Assert\NotBlank()
                ]
            ])
            ->add('price', MoneyType::class, [
                'attr' => [
                    'class' => 'form-control'
                ], 
                'required' => false,
                'label' => 'Prix',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'constraints' => [
                    new Assert\Positive(),
                    new Assert\LessThanOrEqual(1000),
                    new Assert\NotBlank()
                ]
            ])
            ->add('isFavorite', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input'
                ], 
                'label' => 'Favoris',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Type('bool'),
                    new Assert\NotNull()
                ]
            ])
            ->add('isPublic', CheckboxType::class, [
                'attr' => [
                    'class' => 'form-check-input'
                ], 
                'label' => 'Public',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
                'required' => false,
                'constraints' => [
                    new Assert\Type('bool'),
                    new Assert\NotNull()
                ]
            ])
            ->add('ingredients', EntityType::class, [
                'attr' => [
                    'class' => 'form-select'
                ],
                'class' => Ingredient::class,
                'query_builder' => function (IngredientRepository $repository) {
                    return $repository->createQueryBuilder('i')
                        ->where('i.user = :user')
                        ->orderBy('i.name', 'ASC')
                        ->setParameter('user', $this->tokenStorage->getToken()->getUser());
                },
                'label' => 'Les ingrédients',
                'label_attr' => [
                    'class' => 'form-label mt-4'
                ],
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Créer une recette',
                'attr' => [
                    'class' => 'btn btn-primary mt-4'
                ]
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}

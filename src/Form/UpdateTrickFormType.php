<?php

namespace App\Form;;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UpdateTrickFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('name', TextType::class, [
                'label' => 'Trick name',
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'class' => 'form-control'
                ]
            ])

            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'placeholder' => '',
                    'class' => 'form-control'
                ],
                'row_attr' => ['class' => 'description', 'rows' => "8"]
            ])

            ->add('category', EntityType::class, [
                'label' => 'Catégories',
                'class' => Category::class,
                'choice_label' => 'label',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add(
                'spotlight',
                FileType::class,
                [
                    'label' => 'Image in the spotlight',
                    'multiple' => false,
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            )
            ->add(
                'images',
                FileType::class,
                [
                    'label' => 'Images',
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                    'attr' => [
                        'class' => 'form-control'
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "allow_extra_fields" => true
        ]);
    }
}

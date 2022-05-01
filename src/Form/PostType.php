<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => ' ',
                'attr' => [
                    'placeholder' => 'Title',
                    'class' => 'form-control',
                    'minlength' => 6,
                    'maxlength' => 300
                ],
                'required' => true,
                ])

            ->add('descr', TextareaType::class, [
                'label' => ' ',
                'attr' => [
                    'placeholder' => 'Tekst',
                    'rows' => '10',
                    'class' => 'form-control',
                    'minlength' => 6,
                ],
                'required' => true,
            ])

            ->add('img', FileType::class, [
                'label' => ' ',
                'attr' => [
                    'class' => 'form-control'
                ],
                // unmapped means that this field is not associated to any entity property
                'mapped' => false,

                // make it optional so you don't have to re-upload the PDF file
                // every time you edit the Product details
                'required' => false,

                // unmapped fields can't define their validation using annotations
                // in the associated entity, so you can use the PHP constraint classes
                'constraints' => [
                    new File([
                        'maxSize' => '10024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Prosze zaladować prawidlowy obrazek',
                    ])
                ],
            ])// ...
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'csrf_protection' => false,
        ]);
    }
}

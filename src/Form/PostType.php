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
use Symfony\Contracts\Translation\TranslatorInterface;

class PostType extends AbstractType
{

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => ' ',
                'attr' => [
                    'placeholder' => 'post.createTitle',
                    'class' => 'form-control',
                    'minlength' => 6,
                    'maxlength' => 300
                ],
                'required' => true,
                ])

            ->add('descr', TextareaType::class, [
                'label' => ' ',
                'attr' => [
                    'placeholder' => 'post.postText',
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
                        'mimeTypesMessage' => $this->translator->trans('error.imgError'),
                    ])
                ],
            ])// ...
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'post_item',
        ]);
    }
}

<?php

namespace App\Form;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'contactForm.name',
                'attr' => [
                    'placeholder' => 'contactForm.namePlaceholder',
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 50
                ],
                'required' => true,
            ])
            ->add('phone', NumberType::class, [
                'invalid_message' => $this->translator->trans('contactForm.phoneError'),
                'label' => 'contactForm.phone',
                'attr' => [
                    'placeholder' => 'contactForm.phonePlaceholder',
                    'class' => 'form-control',
                    'minlength' => 9,
                    'maxlength' => 11
                ],
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'contactForm.email',
                'attr' => [
                    'placeholder' => 'contactForm.emailPlaceholder',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(['message' => $this->translator->trans('contactForm.emailMistake')]),
                    new Email(['message' => $this->translator->trans('contactForm.emailError')]),
                ],
                'required' => true,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'contactForm.message',
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
                'attr' => [
                    'placeholder' => 'contactForm.messagePlaceholder',
                    'rows' => '10',
                    'class' => 'form-control',
                    'minlength' => 6,
                ],
            'required' => true,
            ])
            ->add('check', CheckboxType::class, [
                'label'    => 'contactForm.checkBox',
                'label_attr' => [
                    'class' => 'form-check-label space',
                ],
                'required' => true,
                'attr' => [
                    'class' => 'form-check-input',
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'contact_item',
        ]);
    }
}

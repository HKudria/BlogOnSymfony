<?php

namespace App\Form;

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

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Imię*',
                'attr' => [
                    'placeholder' => 'Podaj imię',
                    'class' => 'form-control',
                    'minlength' => 2,
                    'maxlength' => 50
                ],
                'required' => true,
            ])
            ->add('phone', NumberType::class, [
                'label' => 'Telefon',
                'attr' => [
                    'placeholder' => 'Podaj numer telefonu',
                    'class' => 'form-control',
                    'minlength' => 9,
                    'maxlength' => 11
                ],
                'help' =>'Prosze podać numer telefonu formacie 555444555',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email adres*',
                'attr' => [
                    'placeholder' => 'Podaj adres email',
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(["message" => "Prosze podać prawidlowy mail"]),
                    new Email(["message" => "Twoj mail nie wygliąda na prawidlowy"]),
                ],
                'required' => true,
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Wiadomość*',
                'label_attr' => [
                    'class' => 'form-check-label',
                ],
                'attr' => [
                    'placeholder' => 'Tekst',
                    'rows' => '10',
                    'class' => 'form-control',
                    'minlength' => 6,
                ],
            'required' => true,
            ])
            ->add('check', CheckboxType::class, [
                'label'    => 'Zatwierdż mnie',
                'label_attr' => [
                    'class' => 'form-check-label',
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
            'csrf_protection' => false,
        ]);
    }
}

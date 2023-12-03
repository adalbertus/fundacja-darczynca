<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('email')
            ->add(
                'first_name', TextType::class,
                [
                    'label' => 'Imię',
                    'empty_data' => '',
                    'required' => true,
                ]
            )
            ->add(
                'last_name', TextType::class,
                [
                    'label' => 'Nazwisko',
                    'empty_data' => '',
                    'required' => true,
                ]
            )

            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => 'Nowe hasło',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Proszę podać hasło',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Hasło musi zawierać przynajmniej {{ limit }} znaków',
                        // max length allowed by Symfony for security reasons
                        'max' => 1024,
                        'maxMessage' => 'Hasło nie może zawierać więcej niż {{ limit }} znaków',
                    ]),
                ],
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
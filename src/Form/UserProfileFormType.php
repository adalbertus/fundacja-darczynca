<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Dto\UserProfileDto;
use App\Repository\UserRepository;
use App\Service\StringHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserProfileFormType extends AbstractType
{
    public function __construct(private UserRepository $userRepository)
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email', EmailType::class,
                [
                    'label' => 'E-mail',
                    'required' => true,
                    'empty_data' => '',
                ]
            )
            ->add(
                'firstName', TextType::class,
                [
                    'label' => 'Imię',
                    'empty_data' => '',
                    'required' => true,
                ]
            )
            ->add(
                'lastName', TextType::class,
                [
                    'label' => 'Nazwisko',
                    'empty_data' => '',
                    'required' => true,
                ]
            )
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'label' => 'Nowe hasło',
                'options' => [
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Proszę podać hasło.',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Hasło musi zawierać przynajmniej {{ limit }} znaków.',
                            'max' => 1024,
                            'maxMessage' => 'Hasło nie może zawierać więcej niż {{ limit }} znaków.',
                        ]),
                    ],
                    'label' => 'Nowe hasło',
                ],
                'second_options' => [
                    'label' => 'Powtórz hasło',
                ],
                'invalid_message' => 'Oba hasła muszą się zgadzać.',
                'required' => $options['password_required'],
                'mapped' => false
            ])
            // ->add('plainPasswordRepeat', PasswordType::class, [
            //     'label' => 'Nowe hasło (powtórka)',
            //     'options' => [
            //         'attr' => [
            //             'autocomplete' => 'new-password',
            //         ],
            //     ],
            //     'required' => $options['password_required'],
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserProfileDto::class,
            'password_required' => false,
        ]);

        $resolver->setAllowedTypes('password_required', 'bool');
    }
}
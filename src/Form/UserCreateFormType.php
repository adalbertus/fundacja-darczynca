<?php

namespace App\Form;

use App\Constants\UserRolesKeys;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'E-mail',
                    'required' => true,
                ]
            )
            ->add('roles', ChoiceType::class, [
                'label' => 'Rola',
                'required' => true,
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    UserRolesKeys::ROLE_DESCRIPTIONS[UserRolesKeys::ADMIN] => UserRolesKeys::ADMIN,
                    UserRolesKeys::ROLE_DESCRIPTIONS[UserRolesKeys::DONOR] => UserRolesKeys::DONOR,
                ]
            ])
            ->add(
                'first_name',
                TextType::class,
                [
                    'label' => 'Imię',
                    'empty_data' => '',
                    'required' => true,
                ]
            )
            ->add(
                'last_name',
                TextType::class,
                [
                    'label' => 'Nazwisko',
                    'empty_data' => '',
                    'required' => true,
                ]
            )
            ->add(
                'comment',
                TextareaType::class,
                [
                    'label' => 'Opis',
                    'required' => false,
                    'attr' => [
                        'rows' => 4
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
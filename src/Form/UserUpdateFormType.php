<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'E-mail',
                    'required' => false,
                ]
            )
            // ->add('roles')
            // ->add('password')
            // ->add('isVerified')
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
            // ->add('is_active')
            // ->add('comment')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
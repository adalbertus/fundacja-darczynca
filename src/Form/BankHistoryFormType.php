<?php

namespace App\Form;

use App\Form\Autocomplete\DonorAutocompleteField;
use App\Entity\BankHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BankHistoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'category',
                HiddenType::class,
                [
                    'error_bubbling' => false,
                ]
            )
            ->add(
                'sub_category',
                HiddenType::class,
                [
                    'error_bubbling' => false,
                ]
            )
            ->add('comment', TextareaType::class, [
                'required' => false,
                'label' => 'Komentarz',
            ])
            ->add('donor', DonorAutocompleteField::class)
            ->add(
                'flagged',
                CheckboxType::class,
                [
                    'required' => false,
                    'label' => 'Oznaczony do przejrzenia później',
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BankHistory::class,
        ]);
    }

}
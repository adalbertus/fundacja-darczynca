<?php

namespace App\Form;

use App\Entity\Donor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use App\Form\Autocomplete\DonorAutocompleteField;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class DonorDeleteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Nazwa',
                    'empty_data' => '',
                    'required' => true,
                    'attr' => ['readonly' => ''],
                ]
            )
            ->add(
                'comment',
                TextareaType::class,
                [
                    'label' => 'Opis',
                    'required' => false,
                    'attr' => [
                        'rows' => 4,
                        'readonly' => ''
                    ],
                ]
            );

        if ($options['donor_transfer']) {
            $builder->add(
                'donor',
                DonorAutocompleteField::class,
                [
                    'label' => 'Przepisanie wpłat na darczyńcę',
                    // 'help' => 'Pozostawienie pustego darczyńcy wygeneruje automatycznego darczyńcę.',
                    'mapped' => false,
                    'constraints' => [new Callback([$this, 'donorValidation'])],
                ]
            );
        }
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donor::class,
            'donor_transfer' => false,
            // 'validation_groups' => false,
        ]);
    }

    public function donorValidation($value, ExecutionContextInterface $context)
    {
        /** @var Donor $value */

        $form = $context->getRoot();
        /** @var Donor $data */
        $data = $form->getData();

        if (!$value instanceof Donor) {
            $context->buildViolation('Wybór darczyńcy jest wymagany.')
                ->atPath('donor')
                ->addViolation();
            return;
        }

        if ($data->getId() == $value->getId()) {
            $context->buildViolation('Wybrany darczyńca jest tym samym, który ma być usunięty.')
                ->atPath('donor')
                ->addViolation();
        }
    }
}
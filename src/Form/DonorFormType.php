<?php

namespace App\Form;

use App\Entity\Donor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class DonorFormType extends AbstractType
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
                ]
            )
            ->add('donorSearchPatterns', CollectionType::class, [
                'entry_type' => DonorSearchPatternFormType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'allow_delete' => true,
            ])
            ->add('autocomplete_email', TextType::class, [
                //https://symfony.com/bundles/ux-autocomplete/current/index.html#using-with-a-texttype-field
                'label' => 'E-mail',
                'mapped' => false,
                'required' => false,
                'autocomplete' => true,
                'empty_data' => '',
                'no_results_found_text' => 'Nieznaleziono wyników',
                'no_more_results_text' => 'Nie ma więcej wyników',
                'tom_select_options' => [
                    'create' => true,
                    'createOnBlur' => true,
                    'maxItems' => 1,
                ],
                // żeby można przetłumaczyć wszystkie templates trzeba ogarnąć to własnym controlerem
                'attr' => ['data-controller' => 'custom-autocomplete'],
                'autocomplete_url' => $options['autocomplete_email_url'],
                'constraints' => [new Callback([$this, 'autocompleteEmailValidation'])],
            ])
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
            );
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Donor::class,
            'autocomplete_email_url' => '',
        ]);
    }

    public function autocompleteEmailValidation($value, ExecutionContextInterface $context)
    {
        // $form = $context->getRoot();
        // $data = $form->getData();

        $emails = explode(';', $value);
        foreach ($emails as $email) {
            $emailConstraint = new Email(message: "Adres e-mail '{$email}' jest nieprawidłowy.");
            $errorList = $context->getValidator()->validate($email, $emailConstraint);
            foreach ($errorList as $error) {
                $context->buildViolation($error->getMessage())
                    ->atPath('autocomplete_email')
                    ->addViolation();
            }
        }
    }
}
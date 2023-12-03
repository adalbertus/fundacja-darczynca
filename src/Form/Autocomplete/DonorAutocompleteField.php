<?php

namespace App\Form\Autocomplete;

use App\Entity\Donor;
use App\Repository\DonorRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
// use Doctrine\Bundle\DoctrineBundle\Enti
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;


#[AsEntityAutocompleteField]
class DonorAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Donor::class,
            'label' => 'Darczyńca',
            'no_results_found_text' => 'Nieznaleziono wyników',
            'no_more_results_text' => 'Nie ma więcej wyników',
            'attr' => [
                'data-bank-history-edit-target' => 'donorSelect',
                'data-action' => 'bank-history-edit#donorChanged',
                'data-controller' => 'custom-autocomplete',
            ],
            // wyłączam dodawanie - ponieważ nie wiem jak potem przypisać takiego darczyńcę do modelu
            // 'tom_select_options' => [
            //     'create' => true,
            //     'createOnBlur' => true,
            //     'delimiter' => ';',
            // ],
            'required' => false,
            'searchable_fields' => ['name'],
            'query_builder' => function (DonorRepository $donorRepository) {
                return $donorRepository->createQueryBuilder('d')
                    ->orderBy('d.name', 'ASC');
            },
            //'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }

}
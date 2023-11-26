<?php

namespace App\Form;

use App\Entity\Provider;
use App\Data\SelectProvider;
use Doctrine\ORM\QueryBuilder;
use App\Repository\ProviderRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProviderCleanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('providerToKeep', EntityType::class, [
                'class' => Provider::class,
                'query_builder' => function (ProviderRepository $providerRepo): QueryBuilder {
                    return $providerRepo->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'nameId',
                'label' => 'Fournisseur à garder',
                'required' => false,
            ])
            ->add('providerToReplace', EntityType::class, [
                'class' => Provider::class,
                'query_builder' => function (ProviderRepository $providerRepo): QueryBuilder {
                    return $providerRepo->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => 'nameId',
                'label' => 'Fournisseur à remplacer',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Remplacer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SelectProvider::class,
        ]);
    }
}

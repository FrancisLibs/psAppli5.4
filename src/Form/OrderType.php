<?php

namespace App\Form;

use App\Entity\Order;
use App\Entity\Provider;
use App\Entity\AccountType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'date', DateType::class, [
                    'input' => 'datetime',
                    'label' => 'Date de commande',
                    'widget' => 'single_text',
                    'required'  => false,
                ]
            )
            ->add('accountType', EntityType::class, [
                'class' => AccountType::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->orderBy('a.letter', 'ASC');
                },
                'choice_label' => 'designation',  // texte affiché
                'multiple' => true,
                'expanded' => true,
                'label' => false,
                'choice_attr' => function(AccountType $accountType) {
                    // ajoute l'attribut data-letter avec la vraie lettre
                    return ['data-letter' => $accountType->getLetter()];
                },
            ])
            ->add(
                'number', IntegerType::class, [
                'label' => 'Numéro',
                'required' => true,
                ]
            )
            ->add(
                'provider', EntityType::class, [
                'class'     => Provider::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label'   =>  'name',
                'required'  => true,
                'label' =>  'Fournisseur'
                ]
            )
            ->add('designation')
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => Order::class,
            ]
        );
    }

}
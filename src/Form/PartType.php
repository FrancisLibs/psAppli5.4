<?php

namespace App\Form;

use App\Entity\Part;
use App\Form\StockType;
use App\Entity\Provider;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('designation')
            ->add('reference')
            ->add('code')
            ->add(
                'remark', TextareaType::class, [
                'label' => 'Remarque',
                'required' => false,
                ]
            )
            ->add('stock', StockType::class)
            ->add(
                'provider', EntityType::class, [
                'class'     => Provider::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label'   =>  'name',
                'required'  => false,
                'label' =>  'Fournisseur'
                ]
            )
            ->add(
                'steadyPrice', NumberType::class, [
                'label' => 'Prix',
                'required' => false,

                ]
            )
            ->add(
                'lastCommandeDate',
                DateType::class,
                [
                    'input' => 'datetime',
                    'label' => 'Date de commande',
                    'widget' => 'single_text',
                    'required'  => false,
                ]
            )
            ->add(
                'maxDeliveryDate',
                DateType::class,
                [
                    'input' => 'datetime',
                    'label' => 'Date de livraison max',
                    'widget' => 'single_text',
                    'required'  => false,
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => Part::class,
            ]
        );
    }
}

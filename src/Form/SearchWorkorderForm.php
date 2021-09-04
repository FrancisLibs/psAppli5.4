<?php

namespace App\Form;

use App\Data\SearchWorkorder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class SearchWorkorderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Status...']
            ])

            ->add('createdAt', DateTimeType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Date...']
            ])

            ->add('machine', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Machine...']
            ])

            ->add('user', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Technicien...']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchWorkorder::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
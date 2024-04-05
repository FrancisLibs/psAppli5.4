<?php

namespace App\Form;

use App\Data\SearchIndicator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class SearchIndicatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'startDate', DateType::class, [
                'widget' => 'single_text', 
                'label'  => false               
                ]
            )

            ->add(
                'endDate', DateType::class, [
                'widget' => 'single_text',  
                'label'  => false              
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            'data_class' => SearchIndicator::class,
            ]
        );
    }
}

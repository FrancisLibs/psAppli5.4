<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Stock1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('place')
            ->add('qteMin')
            ->add('qteMax')
            ->add('qteStock')
            ->add('part');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => Stock::class,
            ]
        );
    }
}

<?php

namespace App\Form;

use App\Entity\Workorder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkorderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('createdAt')
            ->add('status')
            ->add('startDate')
            ->add('endDate')
            ->add('subject')
            ->add('duration')
            ->add('machineStopTime')
            ->add('startTime')
            ->add('endTime')
            ->add('machine')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkOrder::class,
        ]);
    }
}

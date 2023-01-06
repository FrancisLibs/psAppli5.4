<?php

namespace App\Form;

use App\Entity\Oncall;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OnCallType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('callDay', DateType::class, [
                'label' =>  'Jour',
                'widget' => 'single_text',
            ])
            ->add('callTime', TimeType::class, [
                'label' =>  'Heure',
                'widget' => 'single_text',
            ])
            ->add('whoCalls', TextType::class, [
                'label' =>  'Qui'
            ])
            ->add('arrivalTime', TimeType::class, [
                'label' =>  'Heure d\'arrivée',
                'widget' => 'single_text'
            ])
            ->add('reason', TextareaType::class, [
                'label' =>  'Cause'
            ])
            ->add('durationHours', IntegerType::class, [
                'label' =>  'Heures',
            ])
            ->add('durationMinutes', IntegerType::class, [
                'label' =>  'Minutes',
            ])
            ->add('travelhours', IntegerType::class, [
                'label' =>  'Heures',
            ])
            ->add('travelMinutes', IntegerType::class, [
                'label' =>  'Minutes',
            ])
            ->add('task', TextareaType::class, [
                'label' =>  'Action',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Oncall::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\User;
use App\Data\SearchWorkorder;
use App\Entity\WorkorderStatus;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class SearchWorkorderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('machine', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Machine...']
            ])
            ->add('user', EntityType::class, [
                'class'     => User::class,
                'choice_label'   =>  'userName',
                'label'     => false,
                'required'  => false,
                'placeholder' => 'Technicien...',
            ])
            ->add('status', EntityType::class, [
                'class'     => WorkorderStatus::class,
                'choice_label'   =>  'name',
                'label'     => false,
                'required'  => false,
                'placeholder' => 'Status...',
            ])
            ->add('preventive', CheckboxType::class, [
                'label'     => 'Préventif',
                'required'  => false,
            ])
            ->add('closure', CheckboxType::class, [
                'label'     => 'Cloturé',
                'required'  => false,
            ])
        ;

    // Préselection des dates et temps du BT
        // $builder->addEventListener(
        //     FormEvents::PRE_SET_DATA,
        //     function (FormEvent $event) {
        //         $form = $event->getData();
        //         // Preset de startTime
        //         if (is_null($form->getStartDate())) $form->setStartDate(new \DateTime('now'));
        //         if (is_null($form->getStartTime())) $form->setStartTime(new \DateTime('now'));
        //         if (is_null($form->getEndDate()))   $form->setEndDate(new \DateTime('now'));
        //         if (is_null($form->getDurationDay()))   $form->setDurationDay(0);
        //         if (is_null($form->getDurationHour()))   $form->setDurationHour(0);
        //         if (is_null($form->getDurationMinute()))   $form->setDurationMinute(0);
        //         //if (is_null($form->getEndTime())) $form->setEndDate(new \DateTime('21-01-01'));

        //     }
        // );
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

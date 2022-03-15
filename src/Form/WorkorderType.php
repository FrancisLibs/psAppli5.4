<?php

namespace App\Form;

use App\Entity\Workorder;
use Symfony\Component\Form\FormEvent;
use App\Repository\WorkshopRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class WorkorderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => false,
                'choices'  => [
                    'Curatif' => 1,
                    'Préventif' => 2,
                    'Amélioratif' => 3,
                ],
            ])
            ->add('request', TextType::class, [
                'label' => 'Demande'
            ])
            ->add('implementation', TextType::class, [
                'label' => 'Réalisation'
            ])
            ->add('remark', TextareaType::class, [
                'label' => 'Remarque',
                'required' => false,
            ])
            ->add('price', NumberType::class, [
                'label' =>  'Prix sans pièces'
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Date début',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Heure début',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('endDate', DateType::class, [
                'label' => 'Date fin',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Heure fin',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required'  => false,
            ])
            ->add('durationDay', TextType::class, [
                'label' =>  'Jours',
                // 'disabled' =>  true,
            ])
            ->add('durationHour', TextType::class, [
                'label' =>  'Heures'
            ])
            ->add('durationMinute', TextType::class, [
                'label' =>  'Minutes'
            ])
            ->add('stopTimeHour', TextType::class, [
                'label' =>  'Heures',
                'required' => false,
            ])
            ->add('stopTimeMinute', TextType::class, [
                'label' =>  'Minutes',
                'required' => false,
            ]);

        // Préselection des dates et temps du BT
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getData();
                // Preset de startTime
                if (is_null($form->getStartDate())) $form->setStartDate(new \DateTime('now'));
                if (is_null($form->getStartTime())) $form->setStartTime(new \DateTime('now'));
                if (is_null($form->getEndDate()))   $form->setEndDate(new \DateTime('now'));
                if (is_null($form->getDurationDay()))   $form->setDurationDay(0);
                if (is_null($form->getDurationHour()))   $form->setDurationHour(0);
                if (is_null($form->getDurationMinute()))   $form->setDurationMinute(0);
                //if (is_null($form->getEndTime())) $form->setEndDate(new \DateTime('21-01-01'));
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkOrder::class,
            'organisation' => null,
            'translation_domain' => 'forms'
        ]);
    }
}

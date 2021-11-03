<?php

namespace App\Form;

use App\Entity\Schedule;
use App\Entity\Workorder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ScheduleType extends AbstractType
{
    // private $workshopRepository;

    // public function __construct(WorkshopRepository $workshopRepository)
    // {
    //     $this->workshopRepository = $workshopRepository;
    // }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period', IntegerType::class, [
                'label' =>  'Période en jours',
                'required' => true,
            ])
            ->add('nextDate', DateType::class, [
                'label' => 'Date de début',
                'required' => true,
                'widget' => 'single_text'
            ])
            ->add('daysBefore', IntegerType::class, [
                'label' => 'Jours avant événement',
                'required' => true,
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée en heures',
                'required' => false,
            ]);


        // // Préselection des dates et temps du BT
        // $builder->addEventListener(
        //     FormEvents::PRE_SET_DATA,
        //     function (FormEvent $event) {
        //         $form = $event->getData();
        //         // Preset de startTime
        //         if (is_null($form->getStartDate())) $form->setStartDate(new \DateTime('now'));
        //         if (is_null($form->getStartTime())) $form->setStartTime(new \DateTime('now'));
        //         if (is_null($form->getEndDate()))   $form->setEndDate(new \DateTime('now'));
        //        //if (is_null($form->getEndTime())) $form->setEndDate(new \DateTime('21-01-01'));
        //     }
        // );

        // $formModifier = function (FormInterface $form, Workshop $workshop = null) {
        //     $machines = null === $workshop ? [] : $workshop->getMachines();
        //     $form->add('machine', EntityType::class, [
        //         'class' => Machine::class,
        //         'choices' => $machines,
        //         'choice_label'  => 'designation',
        //         'label' => 'Machine'
        //     ]);
        // };

        // $builder->get('workshop')->addEventListener(
        //     FormEvents::POST_SUBMIT,
        //     function (FormEvent $event) use ($formModifier) {
        //         $form = $event->getForm()->getParent();
        //         $workshop = $event->getForm()->getData();
        //         $formModifier($form, $workshop);
        //     }
        // );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Schedule::class,
            'organisation' => null,
            'translation_domain' => 'forms'
        ]);
    }
}

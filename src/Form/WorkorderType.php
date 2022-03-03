<?php

namespace App\Form;

use App\Entity\Machine;
use App\Entity\Workshop;
use App\Entity\Workorder;
use Symfony\Component\Form\FormEvent;
use App\Repository\WorkshopRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class WorkorderType extends AbstractType
{
    private $workshopRepository;

    public function __construct(WorkshopRepository $workshopRepository)
    {
        $this->workshopRepository = $workshopRepository;
    }

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
            ->add('startDate', DateType::class, [
                'input' => 'datetime',
                'label' => 'Date début',
                'widget' => 'single_text',
            ])
            ->add('startTime', TimeType::class, [
                'label' => 'Heure début',
                'widget' => 'single_text',
                'input' => 'datetime',
            ])
            ->add('endDate', DateType::class, [
                'input' => 'datetime',
                'label' => 'Date fin',
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'label' => 'Heure fin',
                'widget' => 'single_text',
                'input' => 'datetime',
                'required'  => false,
            ])
            ->add('durationDay', TextType::class, [
                'label' =>  'jours',
            ])
            ->add('durationHour', TextType::class, [
                'label' =>  'Heures'
            ])
            ->add('durationMinute', TextType::class, [
                'label' =>  'Minutes'
            ])
            ->add('stopTimeHour', TextType::class, [
                'label' => 'Heure',
                'required' => false,
            ])
            ->add('stopTimeMinute', TextType::class, [
                'label' => 'Minute',
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

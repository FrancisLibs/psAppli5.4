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
            // ->add('workshop', EntityType::class, [
            //     'class' => Workshop::class,
            //     'choices' => $this->workshopRepository->findWorkshops($options['organisation']),
            //     'choice_label'  =>  'name',
            //     'label' =>  'Atelier',
            //     'mapped'    =>  false,
            //     'placeholder'   =>  'Atelier...',
            //     'required' => false,
            // ])
            // ->add('machine', EntityType::class, [
            //     'placeholder'   =>  'Machine (Choisir un atelier...)',
            //     'class' => Machine::class,
            //     'choice_label'  =>  'designation',
            // ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
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
                'label' => false,
                'widget' => 'single_text',
            ])
            ->add('startTime', TimeType::class, [
                'label' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                // 'format' => 'HHmm'
            ])
            ->add('endDate', DateType::class, [
                'input' => 'datetime',
                'label' => false,
                'widget' => 'single_text',
            ])
            ->add('endTime', TimeType::class, [
                'label' => false,
                'widget' => 'single_text',
                'input' => 'datetime',
                'required'  => false,
                // 'format' => 'HHmm'
            ])
            ->add('durationDay', TextType::class, [
                'disabled' =>  true,
                'label' =>  'jours',
            ])
            ->add('durationHour', TextType::class, [
                'disabled' =>  true,
                'label' =>  'Heures'
            ])
            ->add('durationMinute', TextType::class, [
                'disabled' =>  true,
                'label' =>  'Minutes'
            ])
            ->add('stopTimeHour', TextType::class, [
                'required' => false,
            ])
            ->add('stopTimeMinute', TextType::class, [
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
               //if (is_null($form->getEndTime())) $form->setEndDate(new \DateTime('21-01-01'));
            }
        );

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
            'data_class' => WorkOrder::class,
            'organisation' => null,
            'translation_domain' => 'forms'
        ]);
    }
}

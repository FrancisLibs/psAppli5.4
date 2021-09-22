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
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class WorkorderEditType extends AbstractType
{
    private $workshopRepository;

    public function __construct(WorkshopRepository $workshopRepository)
    {
        $this->workshopRepository = $workshopRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('machine', EntityType::class, [
                'placeholder'   =>  'Machine (Choisir un atelier...)',
                'class' => Machine::class,
                'choice_label'  =>  'designation',
            ])
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
            ])
        ;
    
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WorkOrder::class,
            'translation_domain' => 'forms'
        ]);
    }
}

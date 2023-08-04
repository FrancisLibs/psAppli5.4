<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Workorder;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class WorkorderType extends AbstractType
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'label' => false,
                    'choices'  => [
                        'Curatif' => 1,
                        'Préventif' => 2,
                        'Amélioratif' => 3,
                    ],
                ]
            )
            ->add(
                'user',
                EntityType::class,
                [
                    'class' => User::class,
                    'choice_label' => 'firstName',
                    'choices' => $this->userRepository->findAllActiveUserByOrganisationAndService($options['userParams']),
                    'label' => false,
                ]
            )
            ->add(
                'request',
                TextType::class,
                ['label' => 'Demande']
            )
            ->add(
                'implementation',
                TextType::class,
                ['label' => 'Réalisation']
            )
            ->add(
                'remark',
                TextareaType::class,
                [
                    'label' => 'Remarque',
                    'required' => false,
                ]
            )
            ->add(
                'startDate',
                DateType::class,
                [
                    'input' => 'datetime',
                    'label' => 'Date début',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'startTime',
                TimeType::class,
                [
                    'label' => 'Heure début',
                    'widget' => 'single_text',
                    'input' => 'datetime',
                ]
            )
            ->add(
                'endDate',
                DateType::class,
                [
                    'input' => 'datetime',
                    'label' => 'Date fin',
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'endTime',
                TimeType::class,
                [
                    'label' => 'Heure fin',
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'required'  => false,
                ]
            )
            ->add(
                'durationDay',
                TextType::class,
                ['label' =>  'Jours']
            )
            ->add(
                'durationHour',
                TextType::class,
                ['label' =>  'Heures']
            )
            ->add(
                'durationMinute',
                TextType::class,
                ['label' =>  'Minutes']
            )
            ->add(
                'stopTimeHour',
                TextType::class,
                [
                    'label' => 'Heures',
                    'required' => false,
                ]
            )
            ->add(
                'stopTimeMinute',
                TextType::class,
                [
                    'label' => 'Minutes',
                    'required' => false,
                ]
            )
            ->add(
                'operationPrice',
                NumberType::class,
                ['label' =>  'Cout opération']
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => WorkOrder::class,
                'userParams' => null,
                'translation_domain' => 'forms'
            ]
        );
    }
}

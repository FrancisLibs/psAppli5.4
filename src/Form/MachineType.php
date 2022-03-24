<?php

namespace App\Form;

use App\Entity\Machine;
use App\Entity\Workshop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class MachineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('internalCode', TextType::class, [
                'label' => 'Code machine'
            ])
            ->add('designation', TextType::class, [
                'label' => 'Désignation'
            ])
            ->add('constructor', TextType::class, [
                'label' => 'Constructeur'
            ])
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'required' => false,
            ])
            ->add('workshop', EntityType::class, [
                'class' =>  Workshop::class,
                'choice_label' => 'name',
                'label' => 'Atelier'
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'Numéro de série',
                'required' => false,
            ])
            ->add('buyDate', DateType::class, [
                'input' => 'datetime',
                'widget' => 'single_text',
                'label' => 'Date d\'achat',
                'required' => false,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo',
            ]) 
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Machine::class,
        ]);
    }
}

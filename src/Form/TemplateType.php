<?php

namespace App\Form;

use App\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class TemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('request', TextType::class, [
                'label' => 'Demande'
            ])
            ->add('remark', TextareaType::class, [
                'label' => 'Remarque',
                'required' => false,
            ])
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
                'label' => 'Jours av. interv.',
                'required' => true,
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Durée en heures',
                'required' => false,
            ])
            ->add('daysBeforeLate', IntegerType::class, [
                'label' => 'Jours de réalisation',
                'required' => false,
            ])
            ->add('sliding', CheckboxType::class, [
                'label' => 'Glissant',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Template::class,
            'organisation' => null,
            'translation_domain' => 'forms'
        ]);
    }
}

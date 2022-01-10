<?php

namespace App\Form;

use App\Entity\DeliveryNote;
use App\Form\DeliveryNotePartType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DeliveryNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'Numéro de BL'
            ])
            ->add('date', DateType::class, [
                'label' => 'Date de BL',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('deliveryNoteParts', CollectionType::class, [
                'entry_type' => DeliveryNotePartType::class,
                'label' => 'Pièces détachées',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DeliveryNote::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\DeliveryNotePart;
use App\Form\DeliveryNotePartType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DeliveryNotePartsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'parts', CollectionType::class, 
                ['entry_type' => DeliveryNotePartType::class]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            'data_class' => DeliveryNotePart::class,
            ]
        );
    }
}

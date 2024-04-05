<?php

namespace App\Form;

use App\Entity\Part;
use App\Entity\DeliveryNotePart;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class DeliveryNotePartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'part', EntityType::class, [
                'label' => false,
                'class' => Part::class,
                'choice_label' => 'designation',
                ]
            )
            ->add(
                'quantity', IntegerType::class, [
                'label' => false,
                ]
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

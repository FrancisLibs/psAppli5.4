<?php

namespace App\Form;

use App\Entity\User;
use App\Data\SearchWorkorder;
use App\Entity\WorkorderStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class SearchWorkorderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('machine', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Machine...']
            ])

            ->add('user', EntityType::class, [
                'class'     => User::class,
                'choice_label'   =>  'userName',
                'label'     => false,
                'required'  => false,
                'placeholder' => 'Technicien...',
            ])

            ->add('status', EntityType::class, [
                'class'     => WorkorderStatus::class,
                'choice_label'   =>  'name',
                'label'     => false,
                'required'  => false,
                'placeholder' => 'Status...',

            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchWorkorder::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}

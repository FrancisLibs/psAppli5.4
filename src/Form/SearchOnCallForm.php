<?php

namespace App\Form;

use App\Entity\User;
use App\Data\SearchOnCall;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SearchOnCallForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'user', EntityType::class, [
                'class'     => User::class,
                'choice_label'   =>  'userName',
                'label'     => false,
                'required'  => false,
                'placeholder' => 'Technicien...',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => SearchOnCall::class,
            'method' => 'GET',
            'csrf_protection' => false
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

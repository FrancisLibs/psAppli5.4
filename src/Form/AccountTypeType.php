<?php

namespace App\Form;

use App\Entity\AccountType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AccountTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'designation', TextType::class, [
                'label' => 'Désignation',
                ]
            )
            ->add(
                'letter', TextType::class, [
                'label' => 'Lettre',
                ]
            )
            ->add(
                'accountNumber', TextType::class, [
                'label' => 'Numéro de compte',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
            'data_class' => AccountType::class,
            ]
        );
    }
}

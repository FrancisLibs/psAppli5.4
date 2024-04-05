<?php

namespace App\Form;

use App\Entity\User;
use App\Data\SearchProvider;
use App\Data\SearchWorkorder;
use App\Entity\WorkorderStatus;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class SearchProviderForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'name', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Nom...'],
                ]
            )
            ->add(
                'city', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Ville...'],
                ]
            )
            ->add(
                'code', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Code...'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => SearchProvider::class,
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

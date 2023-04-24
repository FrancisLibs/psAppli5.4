<?php

namespace App\Form;

use App\Data\SearchPart;
use App\Entity\Organisation;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchPartForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        //$organisation = $options['organisation'];

        $builder
            ->add('organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'designation',
                'label'     => false,
                'required'  => false,
            ])
            ->add('code', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Code...']
            ])
            ->add('designation', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Désignation...']
            ])
            ->add('reference', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Référence...']
            ])
            ->add('place', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Emplacement...']
            ])
        ;

        // Préselection des champs de formulaires de recherche
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getData();
                if (is_null($form->organisation)) $form->organisation=($options['organisation']);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SearchPart::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'organisation' => null,
            'selectPart' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Provider;
use App\Data\SearchOrder;
use App\Entity\AccountType;
use App\Entity\Organisation;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SearchOrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $organisation = $options['organisation'];

        $builder
            ->add(
                'designation', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Désignation...']
                ]
            )
            ->add(
                'number', TextType::class, [
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Numéro...']
                ]
            )
            ->add(
                'date', DateType::class, [
                'widget' => 'single_text',
                'label'     => false,
                'required'  => false,
                'attr'      => ['placeholder' => 'Date...']
                ]
            )
            ->add(
                'provider', EntityType::class, [
                'class'     => Provider::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('p')->orderBy('p.name', 'ASC');
                    },
                'choice_label' => 'name',
                'attr'      => ['placeholder' => 'Fournisseur...']
                ]
            )->add(
                'accountType', EntityType::class, [
                'class'     => AccountType::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('a')->orderBy('a.designation', 'ASC');
                    },
                'choice_label' => 'designation',
                'attr'      => ['placeholder' => 'Type de compte...']
                ]
            )
            ->add('createdBy', EntityType::class, [
                'class' => User::class,
                'query_builder' => fn(UserRepository $ur) => 
                    $ur->findAllUsersByOrganisationAndActive($organisation),
                'choice_label' => 'username',
                'placeholder' => 'Auteur...', // <-- au lieu de attr
                'required' => false,
            ]);

        // Préselection des champs de formulaires de recherche
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($options) {
                $form = $event->getData();
                if (is_null($form->organisation)) { $form->organisation=($options['organisation']);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => SearchOrder::class,
            'method' => 'GET',
            'csrf_protection' => false,
            'organisation' => null,
            'selectPart' => null,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}

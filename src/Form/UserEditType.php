<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Service;
use App\Entity\Organisation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email...'],
                'required' => true,
                ]
            )
            ->add(
                'phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['placeholder' => 'Numéro de téléphone...'],
                ]
            )
            ->add(
                'firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom...'],
                'required' => true,
                ]
            )
            ->add(
                'lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom...'],
                'required' => true,
                ]
            )
            ->add(
                'organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'designation',
                'multiple' => false,
                'expanded' => true,
                ]
            )
            ->add(
                'service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
                ]
            )
            ->add(
                'imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => User::class,
            ]
        );
    }
}

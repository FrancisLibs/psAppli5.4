<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Organisation;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Identifiant',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit avoir au moins {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom...'],
                'required' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom...'],
                'required' => true,
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Téléphone',
                'attr' => ['placeholder' => 'Numéro de téléphone...'],
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'attr' => ['placeholder' => 'Email...'],
                'required' => true,
            ])
            ->add('organisation', EntityType::class, [
                'class' => Organisation::class,
                'choice_label' => 'designation',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('service', EntityType::class, [
                'class' => Service::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'label' => 'Photo',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

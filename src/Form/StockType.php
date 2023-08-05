<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class StockType extends AbstractType
{
    private $_security;

    public function __construct(Security $security)
    {
        $this->_security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'place', TextType::class, 
                ['label' => 'Emplacement']
            )
            ->add('qteMin')
            ->add('qteMax')
            ->add(
                'approQte', IntegerType::class, 
                ['label' => 'Qte en commande']
            );

        // grab the user, do a quick sanity check that one exists
        $user = $this->_security->getUser();
        if (!$user) {
            throw new \LogicException(
                'The FriendMessageFormType cannot be used without an authenticated user!'
            );
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($user) {
                $roles = $user->getRoles();
                $form = $event->getForm();

                if (in_array('ROLE_ADMIN', $roles)) {
                    $form->add(
                        'qteStock',
                        NumberType::class,
                    );
                } else {
                    $form->add(
                        'qteStock',
                        NumberType::class,
                        ['disabled'   => true]
                    );
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            ['data_class' => Stock::class]
        );
    }
}

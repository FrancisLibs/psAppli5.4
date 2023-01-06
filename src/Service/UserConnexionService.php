<?php

namespace App\Service;

use App\Entity\Connexion;
use Doctrine\ORM\EntityManagerInterface;


class UserConnexionService
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function registration($user)
    {
        // Création d'un enregistrement des connexions
        $date = new \DateTime();
        $connexion = new Connexion();
        $connexion
            ->setDate($date)
            ->setUser($user);

        $this->manager->persist($connexion);
        $this->manager->flush();

        return;
    }
}

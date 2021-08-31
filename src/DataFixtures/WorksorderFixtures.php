<?php

namespace App\DataFixtures;

use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class WorksorderFixtures extends Fixture
{
    private $Urepo;

    public function __construct(UserRepository $userRepository)
    {
        $this->Urepo = $userRepository;
    }

    public function load(ObjectManager $manager)
    {
        
        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Part;
use App\Entity\User;
use App\Entity\Stock;
use App\Entity\Machine;
use App\Entity\Workshop;
use App\Entity\WorkOrder;
use App\Entity\Organisation;
use App\Repository\MachineRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $manager;
    private $URepo;
    private $MRepo;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $URepo,
        MachineRepository $MRepo
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->manager = $entityManager;
        $this->URepo = $URepo;
        $this->MRepo = $MRepo;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        //ORGANISATIONS
        $organisationArray = ['Pierre Schmidt W1', 'Pierre Schmidt W2', 'Pierre Schmidt R2', 'Stoeffler'];
        foreach ($organisationArray as $org) {
            $organisation = (new Organisation())->setDesignation($org);
            $manager->persist($organisation);
            //WORKSHOP
            $workshops = ['boucherie', 'conditionnement', 'mise en cartons', 'charcuterie', 'préparation mêlée'];
            for ($i = 0; $i < 5; $i++) {
                $workshop = (new Workshop());
                $workshop->setName(array_rand($workshops, 1))
                    ->setOrganisation($organisation);
                $manager->persist($workshop);

                //MACHINES
                for ($i = 0; $i < 5; $i++) {
                    $machine = (new Machine());
                    $machine->setConstructor(strtoupper($faker->word))
                        ->setModel(strtoupper($faker->word))
                        ->setSerialNumber($faker->word)
                        ->setWorkshop($workshop);
                    $manager->persist($machine);
                }
            }
            //USERS
            for ($i = 0; $i < 5; $i++) {
                $user  = (new User());
                $user->setUsername($faker->lastName)
                    ->setPhoneNumber($faker->phoneNumber)
                    ->setFirstName($faker->firstName)
                    ->setLastName($faker->lastName)
                    ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                    ->setRoles(['ROLE_USER'])
                    ->setOrganisation($organisation)
                    ->setEmail($faker->email);
                $this->manager->persist($user);
            }

            //PARTS
            for ($i = 0; $i < 20; $i++) {
                $part = (new Part());
                $stock = (new Stock());
                $stock->setPart($part)
                    ->setPlace(
                        strtoupper($faker->randomElement($array = array('a', 'b', 'c'))) .
                            strtoupper($faker->randomElement($array = array('a', 'b', 'c'))) .
                            $faker->randomNumber(3, false)
                    )
                    ->setQteStock($faker->randomNumber(2, false))
                    ->setQteMin($faker->randomNumber(2, false))
                    ->setQteMax($faker->randomNumber(2, false));
                $manager->persist($stock);
                $part->setCode($faker->regexify('^C([A-Z]){4}([0-9]){5}$'))
                    ->setDesignation($faker->sentence(8, true))
                    ->setOrganisation($organisation)
                    ->setReference($faker->uuid)
                    ->setValidity(true)
                    ->setStock($stock);
                $manager->persist($part);
            }
            // //WORKORDERS
            // $machines = $this->MRepo->findAll();
            // $machineArray = array();
            // foreach ($machines as $machine) {
            //     $machineArray [] = $machine;
            // }

            // $users = $this->URepo->findAll();
            // $userArray = array();
            // foreach ($users as $user) {
            //     $userArray[] = $user;
            // }

            // for ($i = 0; $i < 3; $i++) {
            //     $workOrder = (new WorkOrder());
            //     $workOrder->setCreatedAt($faker->dateTime('now', null));
            //     $machine = array_rand($machineArray, 1);
            //     $workOrder->setMachine($machine);
            //     $user = array_rand($userArray, 1);
            //     $workOrder->setUser($user);
            //     $workOrder->setStartDate($faker->dateTime('now', null));
            //     $workOrder->setStatus('open');
            // }
        }
        $this->manager->flush();
    }
}

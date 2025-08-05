<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Part;
use App\Entity\User;
use App\Entity\Stock;
use App\Entity\Machine;
use App\Entity\Workshop;
use App\Entity\Workorder;
use App\Entity\Organisation;
use App\Repository\MachineRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    protected $passwordHasher;
    protected $manager;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->manager = $entityManager;
    }

    public function load(ObjectManager $manager):void
    {
        $faker = Faker\Factory::create('fr_FR');

        //ORGANISATIONS
        $organisationArray = ['Pierre Schmidt W1', 'Pierre Schmidt W2', 'Pierre Schmidt R2', 'Stoeffler'];
        foreach ($organisationArray as $org) {
            $organisation = (new Organisation())
                ->setDesignation($org);
            $manager->persist($organisation);
            //WORKSHOP
            $workshops = ['boucherie', 'conditionnement', 'mise en cartons', 'charcuterie', 'préparation mêlée'];
            for ($i = 0; $i < 5; $i++) {
                $workshop = (new Workshop());
                $workshop->setName($workshops[array_rand($workshops, 1)])
                    ->setOrganisation($organisation);
                $manager->persist($workshop);

                //MACHINES
                $designation = [
                    'Thermoformeuse', 'Clippeuse', 'Poussoir', 'Ligne d\'accrochage',
                    'Balance', 'Bascule', 'Cutter', 'Machine à glace'
                ];
                for ($i = 0; $i < 5; $i++) {
                    $machine = (new Machine());
                    $machine->setConstructor(strtoupper($faker->word))
                        ->setModel(strtoupper($faker->word))
                        ->setSerialNumber($faker->word)
                        ->setWorkshop($workshop)
                        ->setStatus(1)
                        ->setDesignation($designation[array_rand($designation, 1)]);
                    $manager->persist($machine);
                }
            }
            //USERS
            $user  = (new User());
            $user->setUsername('admin' . substr($organisation->getDesignation(), -2))
                ->setPhoneNumber($faker->phoneNumber)
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_ADMIN'])
                ->setOrganisation($organisation)
                ->setEmail($faker->email);
            $this->manager->persist($user);

            $user  = (new User());
            $user->setUsername('user' . substr($organisation->getDesignation(), -2))
                ->setPhoneNumber($faker->phoneNumber)
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'))
                ->setRoles(['ROLE_USER'])
                ->setOrganisation($organisation)
                ->setEmail($faker->email);
            $this->manager->persist($user);

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
        }
        $this->manager->flush();
    }
}

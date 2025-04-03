<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hashI;
    private $faker;

    public function  __construct(
        UserPasswordHasherInterface $hashI,
    ){
        $this->hashI = $hashI;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@hell.com');
        $admin->setFirstName('Hell');
        $admin->setLastName('Admin');
        $admin->setPseudo('admin');
        $admin->setPassword($this->hashI->hashPassword($admin, 'Hell123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);;

        $user = new User();
        $user->setEmail('ariel@hell.com');
        $user->setFirstName('Ariel');
        $user->setLastName('Hell');
        $user->setPseudo('Petite sirène');
        $user->setPassword($this->hashI->hashPassword($user, 'Disney'));
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);

        $manager->persist($admin);
        $manager->persist($user);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($this->faker->email);
            $user->setFirstName($this->faker->firstName);
            $user->setLastName($this->faker->lastName);
            $user->setPseudo($this->faker->userName);
            $user->setPassword($this->hashI->hashPassword($user, '123456'));
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified(true);

            $manager->persist($user);
        }

        $manager->flush();
    }
}
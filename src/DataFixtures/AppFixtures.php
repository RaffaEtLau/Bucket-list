<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $manager): void
    {
        // Faker pour générer des données aléatoires
        $faker = Factory::create('fr_FR');

        $user = new User();
        $user->setEmail('user@test.com');
        $user->setFirstName('User');
        $user->setLastName('Test');
        $user->setPseudo('test');
        $user->setPassword($this->hasher->hashPassword($user, 'usertest'));
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);

        $manager->persist($user);
        $manager->flush();

        // Liste de titres réalistes pour une bucket list
        $bucketListTitles = [
            "Saut en parachute",
            "Apprendre à jouer du piano",
            "Road trip en Californie",
            "Faire un safari au Kenya",
            "Apprendre une nouvelle langue",
            "Courir un marathon",
            "Nager avec des dauphins",
            "Visiter la Grande Muraille de Chine",
            "Faire du bénévolat à l'étranger",
            "Écrire un livre",
            "Explorer les pyramides d'Égypte",
            "Voir le Grand Canyon",
        ];

        // Créer des souhaits
        foreach ($bucketListTitles as $title) {
            $wish = new Wish();
            $wish->setTitle($title);
            $wish->setDescription($faker->paragraphs(2, true));
            $wish->setIsPublished(true);
            $wish->setDateCreated(new \DateTimeImmutable($faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s')));
            $wish->setUser($user);

            // Date de mise à jour pour certains souhaits
            if ($faker->boolean(30)) {
                $wish->setDateUpdated(new \DateTimeImmutable($faker->dateTimeBetween($wish->getDateCreated()->format('Y-m-d H:i:s'), 'now')->format('Y-m-d H:i:s')));
            }

            $manager->persist($wish);
        }

        $manager->flush();
    }
}
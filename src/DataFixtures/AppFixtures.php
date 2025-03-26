<?php

namespace App\DataFixtures;

use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Faker pour générer des données aléatoires
        $faker = Factory::create('fr_FR');

        // Liste de titres réalistes pour une bucket list
        $bucketListTitles = [
            "Voyage au Japon",
            "Saut en parachute",
            "Apprendre à jouer du piano",
            "Road trip en Californie",
            "Faire un safari au Kenya",
            "Voir les aurores boréales en Islande",
            "Apprendre une nouvelle langue",
            "Courir un marathon",
            "Nager avec des dauphins",
            "Visiter la Grande Muraille de Chine",
            "Faire du bénévolat à l'étranger",
            "Écrire un livre",
            "Explorer les pyramides d'Égypte",
            "Voir le Grand Canyon",
            "Vol en planeur",
            "Voyage en Islande"
        ];

        // Créer des souhaits
        foreach ($bucketListTitles as $title) {
            $wish = new Wish();
            $wish->setTitle($title);
            $wish->setDescription($faker->paragraphs(2, true));
            $wish->setAuthor($faker->firstName);
            $wish->setIsPublished(true);
            $wish->setDateCreated(new \DateTimeImmutable($faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s')));

            // Date de mise à jour pour certains souhaits
            if ($faker->boolean(30)) {
                $wish->setDateUpdated(new \DateTimeImmutable($faker->dateTimeBetween($wish->getDateCreated()->format('Y-m-d H:i:s'), 'now')->format('Y-m-d H:i:s')));
            }

            $manager->persist($wish);
        }

        $manager->flush();
    }
}
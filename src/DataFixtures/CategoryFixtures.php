<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Wish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Travel & Adventure',
            'Sport',
            'Entertainment',
            'Human Relations',
            'Others'
        ];

        foreach ($categories as $categoryName) {
            $category = new Category();
            $category->setName($categoryName);
            $manager->persist($category);

            $this->addReference('category'.strtolower(str_replace(' & ', '_', $categoryName)), $category);
        }

        $manager->flush();
    }
}
<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        // Create 20 sample products
        for ($i = 0; $i < 20; $i++) {
            $product = new Product;
            $product->setName(ucwords($this->faker->word()));
            $product->setDescription($this->faker->text());
            $product->setPrice($this->faker->randomFloat(2, 300, 700));
            $product->setCategory($this->faker->word());
            $manager->persist($product);
        }

        $manager->flush();
    }
}

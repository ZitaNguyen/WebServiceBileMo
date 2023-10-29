<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var Generator
     */
    private Generator $faker;
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
         // Clients - authentication
         $clients = [];
         for ($i = 0; $i < 10; $i++) {
             $client = new Client();
             $client->setName($this->faker->name())
                 ->setEmail($this->faker->email())
                 ->setRoles(['ROLE_USER'])
                 ->setPassword($this->userPasswordHasher->hashPassword($client, "test"));

             $clients[] = $client;
             $manager->persist($client);
         }

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

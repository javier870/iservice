<?php

namespace App\DataFixtures;

use App\Factory\VehicleFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VehicleFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        VehicleFactory::createMany(100);
        echo "\n\n 100 vehicles were added to the DB.\n\n";
        // $product = new Product();
        // $manager->persist($product);

        //$manager->flush();
    }
}

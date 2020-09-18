<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            ProductCategoryFixtures::class
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        $diaperSizes = [
            'NB' => 'Newborn',
            'S1' => 'Size 1',
            'M2' => 'Size 2',
            'M3' => 'Size 3',
            'L4' => 'Size 4',
            'XL5' => 'Size 5',
            'XL6' => 'Size 6'
        ];

        foreach ($diaperSizes as $sku => $name) {
            $product = $this->createDiaper($name);
            $product->setSku($sku);

            $manager->persist($product);
        }

        $trainingPantsSizes = [
            'P2' => '2/3',
            'P3' => '3/4',
            'P4' => '4/5'
        ];

        foreach ($trainingPantsSizes as $sku => $name) {
            $product = $this->createTrainingPants($name);
            $product->setSku($sku);

            $manager->persist($product);
        }

        $merchandiseItems = [
            'MUG' => 'Mug',
            'TSHIRT' => 'T-Shirt',
        ];

        foreach ($merchandiseItems as $sku => $name) {
            $product = new Product($name, $this->getReference('product_category_merchandise'));
            $product->setSku($sku);

            $manager->persist($product);
        }

        $manager->flush();
    }

    private function createDiaper(string $name): Product
    {
        $diaper = new Product($name, $this->getReference('product_category_diapers'));
        $diaper->setAgencyPackSize(25);
        $diaper->setAgencyMaxPacks(2);
        $diaper->setAgencyPacksPerBag(12);
        $diaper->setHospitalPackSize(75);
        $diaper->setHospitalPacksPerBag(6);
        $diaper->setDefaultCost(0.13);
        $diaper->setRetailPrice(0.37);
        $diaper->setColor($this->faker->hexColor);
        $diaper->setOrderIndex(0);

        return $diaper;
    }

    private function createTrainingPants(string $name): Product
    {
        $training_pants = new Product($name, $this->getReference('product_category_training_pants'));
        $training_pants->setAgencyPackSize(30);
        $training_pants->setAgencyMaxPacks(1);
        $training_pants->setAgencyPacksPerBag(8);
        $training_pants->setDefaultCost(0.13);
        $training_pants->setRetailPrice(0.37);
        $training_pants->setColor($this->faker->hexColor);
        $training_pants->setOrderIndex(0);

        return $training_pants;
    }
}

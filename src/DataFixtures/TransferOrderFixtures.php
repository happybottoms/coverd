<?php

namespace App\DataFixtures;

use App\Entity\Orders\TransferOrder;
use App\Entity\Orders\TransferOrderLineItem;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\StorageLocation;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TransferOrderFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            ProductFixtures::class,
            PartnerFixtures::class,
            WarehouseFixtures::class
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        $storageLocations = $manager->getRepository(StorageLocation::class)->findAll();
        $partners = $manager->getRepository(Partner::class)->findAll();
        $products = $manager->getRepository(Product::class)->findByPartnerOrderable();

        for ($i = 0; $i < 10; $i++) {
            $storageLocation = $this->faker->randomElement($storageLocations);
            $partner = $this->faker->randomElement($partners);

            while ($storageLocation->getId() == $partner->getId()) {
                $partner = $this->faker->randomElement($partners);
            }

            $order = new TransferOrder(
                $partner,
                $storageLocation
            );

            $order->setCreatedAt($this->faker->dateTimeBetween('-1 year', 'now'));
            $order->setStatus($this->faker->randomElement(TransferOrder::STATUSES));

            foreach ($products as $product) {
                $lineItem = new TransferOrderLineItem($product, $this->faker->numberBetween(100, 1000));
                $order->addLineItem($lineItem);
            }

            $order->generateTransactions();

            if ($order->isComplete()) {
                $order->commitTransactions();
            }

            $manager->persist($order);
        }

        $manager->flush();
    }
}

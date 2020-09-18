<?php

namespace App\DataFixtures;

use App\Entity\Orders\SupplyOrder;
use App\Entity\Orders\SupplyOrderLineItem;
use App\Entity\Product;
use App\Entity\Supplier;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SupplyOrderFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            ProductFixtures::class,
            SupplierFixtures::class,
            WarehouseFixtures::class
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        $suppliers = $manager->getRepository(Supplier::class)->findAll();
        $products = $manager->getRepository(Product::class)->findByPartnerOrderable();

        for ($i = 0; $i < 50; $i++) {
            /** @var Supplier $supplier */
            $supplier = $this->faker->randomElement($suppliers);

            $order = new SupplyOrder(
                $supplier,
                $this->getReference('warehouse_main')
            );

            $order->setSupplierAddress($this->faker->randomElement($supplier->getAddresses()));

            $order->setStatus($this->faker->randomElement([
                SupplyOrder::STATUS_ORDERED,
                SupplyOrder::STATUS_RECEIVED,
                SupplyOrder::STATUS_RECEIVED,
                SupplyOrder::STATUS_RECEIVED,
            ]));

            $order->setCreatedAt($this->faker->dateTimeBetween('-1 year', 'now'));
            foreach ($products as $product) {
                $lineItem = new SupplyOrderLineItem($product, $this->faker->numberBetween(2, 3000));
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

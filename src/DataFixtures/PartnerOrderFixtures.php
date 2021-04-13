<?php

namespace App\DataFixtures;

use App\Entity\Orders\PartnerOrder;
use App\Entity\Orders\PartnerOrderLineItem;
use App\Entity\Partner;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PartnerOrderFixtures extends BaseFixture implements DependentFixtureInterface
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
        $partners = $manager->getRepository(Partner::class)->findAll();
        /** @var Product[] $products */
        $products = $manager->getRepository(Product::class)->findByPartnerOrderable();

        for ($i = 0; $i < 50; $i++) {
            /** @var Partner $partner */
            $partner = $this->faker->randomElement($partners);

            $order = new PartnerOrder(
                $partner,
                $this->getReference('warehouse_main')
            );

            $order->setStatus($this->faker->randomElement(PartnerOrder::STATUSES));

            $order->setCreatedAt($this->faker->dateTimeBetween('-1 year', 'now'));

            $period = new \Moment\Moment($order->getCreatedAt()->format('U'));
            $period->setTimezone($order->getCreatedAt()->getTimezone()->getName());
            $period->startOf('month');
            $order->setOrderPeriod($period);

            foreach ($products as $product) {
                $lineItem = new PartnerOrderLineItem(
                    $product,
                    $this->faker->numberBetween(1, 100) * $product->getSmallestPackSize()
                );
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

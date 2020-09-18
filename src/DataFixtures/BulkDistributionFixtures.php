<?php

namespace App\DataFixtures;

use App\Entity\Orders\BulkDistribution;
use App\Entity\Orders\BulkDistributionLineItem;
use App\Entity\Partner;
use App\Entity\Product;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Moment\Moment;

class BulkDistributionFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            ProductFixtures::class,
            PartnerFixtures::class,
            ClientFixtures::class,
            SettingFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        $partners = $manager->getRepository(Partner::class)->findAll();
        /** @var Product[] $products */
        $products = $manager->getRepository(Product::class)->findByPartnerOrderable();

        // Number of distributions to make for each partner
        $ordersPerPartner = 1;

        foreach ($partners as $partner) {
            $clients = $partner->getClients();
            for ($i = 0; $i < $ordersPerPartner; $i++) {
                $order = new BulkDistribution($partner);

                $order->setStatus($this->faker->randomElement([
                    BulkDistribution::STATUS_COMPLETED,
                    BulkDistribution::STATUS_PENDING,
                ]));

                // If this is the current month, set the order to pending, otherwise completed
                $status = $i == 0 ? BulkDistribution::STATUS_PENDING : BulkDistribution::STATUS_COMPLETED;
                $order->setStatus($status);

                $orderDate = new Moment();
                $orderDate->subtractMonths($i);

                $order->setCreatedAt($orderDate);

                $period = clone $orderDate;
                $period->setTimezone($order->getCreatedAt()->getTimezone()->getName());
                $period->startOf('month');
                $order->setDistributionPeriod($period);

                foreach ($clients as $client) {
                    /** @var Product $product */
                    $product = $this->faker->randomElement($products);
                    $lineItem = new BulkDistributionLineItem(
                        $product,
                        $product->getAgencyMaxPacks() * $product->getAgencyPackSize()
                    );
                    $lineItem->setClient($client);
                    $order->addLineItem($lineItem);
                }

                $order->generateTransactions();

                if ($order->isComplete()) {
                    $order->commitTransactions();
                }

                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}

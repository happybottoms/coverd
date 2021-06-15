<?php

namespace App\DataFixtures;

use App\Entity\Partner;
use App\Entity\PartnerContact;
use App\Entity\PartnerDistributionMethod;
use App\Entity\PartnerFulfillmentPeriod;
use App\Entity\StorageLocationAddress;
use App\Entity\StorageLocationContact;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Workflow\Registry;

class PartnerFixtures extends BaseFixture implements FixtureGroupInterface
{
    /** @var Registry */
    protected $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public static function getGroups(): array
    {
        return ['MigratedFixtures'];
    }

    public function loadData(ObjectManager $manager)
    {
        $periods = [
            new PartnerFulfillmentPeriod('Week A'),
            new PartnerFulfillmentPeriod('Week B'),
            new PartnerFulfillmentPeriod('Week C'),
            new PartnerFulfillmentPeriod('Week D'),
        ];

        $distributionMethods = [
            new PartnerDistributionMethod('Pickup'),
            new PartnerDistributionMethod('Delivery'),
        ];

        foreach ($periods as $period) {
            $manager->persist($period);
        }

        foreach ($distributionMethods as $distributionMethod) {
            $manager->persist($distributionMethod);
        }

        $statuses = Partner::STATUSES;

        for ($i = 0; $i < 10; $i++) {
            $partner = new Partner($this->faker->company . ' Partner', $this->workflowRegistry);
            $partner->setPartnerType(Partner::TYPE_AGENCY);
            $partner->setStatus($this->faker
                ->optional(.5, Partner::STATUS_ACTIVE)
                ->randomElement($statuses));
            $partner->setDistributionMethod($this->randValue($distributionMethods));
            $partner->setFulfillmentPeriod($this->randValue($periods));
            /** @var PartnerContact $contact */
            $contact = $this->createContact(PartnerContact::class);
            $contact->setIsProgramContact(true);
            $partner->addContact($contact);
            $partner->setAddress($this->createAddress(StorageLocationAddress::class));

            $manager->persist($partner);
        }

        $numStatuses = count($statuses);
        for ($i = 0; $i < ($numStatuses * 2); $i++) {
            $hospital = new Partner($this->faker->company . ' Hospital', $this->workflowRegistry);
            $hospital->setPartnerType(Partner::TYPE_HOSPITAL);
            $hospital->setStatus($this->faker
                ->optional(.5, Partner::STATUS_ACTIVE)
                ->randomElement($statuses));
            $hospital->setDistributionMethod($this->randValue($distributionMethods));
            $hospital->setFulfillmentPeriod($this->randValue($periods));
            /** @var PartnerContact $contact */
            $contact = $this->createContact(PartnerContact::class);
            $contact->setIsProgramContact(true);
            $hospital->addContact($contact);
            $hospital->setAddress($this->createAddress(StorageLocationAddress::class));

            $manager->persist($hospital);
        }

        $manager->flush();
    }

    private function randValue($array)
    {
        shuffle($array);
        return reset($array);
    }
}

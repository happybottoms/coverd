<?php

namespace App\DataFixtures;

use App\Entity\EAV\PartnerProfileDefinition;
use App\Entity\Partner;
use App\Entity\PartnerProfile;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PartnerProfileFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            PartnerFixtures::class,
            PartnerProfileAttributeFixtures::class
        ];
    }

    public function loadData(ObjectManager $manager)
    {
        $partners = $manager->getRepository(Partner::class)->findAll();
        $definitions = $manager->getRepository(PartnerProfileDefinition::class)->findAll();

        foreach ($partners as $partner) {
            $profile = new PartnerProfile();
            $profile->setPartner($partner);

            foreach ($definitions as $definition) {
                $attribute = $definition->createAttribute();
                $attribute->setValue($attribute->fixtureData());
                $profile->addAttribute($attribute);
            }
            $manager->persist($profile);
        }

        $manager->flush();
    }
}

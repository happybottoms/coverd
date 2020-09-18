<?php

namespace App\DataFixtures;

use App\Entity\Partner;
use App\Entity\User;
use App\Entity\ValueObjects\Name;
use Doctrine\Persistence\ObjectManager;

class PartnerUserFixtures extends BaseFixture
{
    public function getDependencies(): array
    {
        return [
            GroupFixtures::class,
            PartnerFixtures::class,
        ];
    }

    public function loadData(ObjectManager $manager): void
    {
        $partners = $manager->getRepository(Partner::class)->findAll();

        foreach ($partners as $partner) {
            $domain = preg_filter('/[^a-zA-Z0-9\-]/', '', strtolower($partner->getTitle())) . '.com';
            $partnerUser = new User('user@' . $domain);
            $partnerUser->setName(new Name($this->faker->firstName, $this->faker->lastName));
            $partnerUser->setPartners([$partner])
                ->setPlainTextPassword('password');
            $partnerUser->setGroups([$this->getReference('group_partner')]);

            $manager->persist($partnerUser);
        }

        $manager->flush();
    }
}

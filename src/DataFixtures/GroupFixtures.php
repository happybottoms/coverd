<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Group;
use App\Entity\Order;
use App\Entity\Partner;
use App\Entity\Product;
use App\Entity\Supplier;
use App\Entity\User;
use App\Entity\Warehouse;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends BaseFixture
{
    public function loadData(ObjectManager $em)
    {
        $sysAdmin = new Group();
        $sysAdmin->setName('System Administrator');
        $sysAdmin->setRoles(Group::AVAILABLE_ROLES);
        $em->persist($sysAdmin);
        $this->setReference('group_system_administrator', $sysAdmin);

        $bankManager = new Group();
        $bankManager->setName('Manager');
        $bankManager->setRoles(Group::AVAILABLE_ROLES);
        $em->persist($bankManager);
        $this->setReference('group_manager', $bankManager);

        $volunteer = new Group();
        $volunteer->setName('Volunteer');
        $volunteer->setRoles([
            Order::ROLE_EDIT_ALL,
            Order::ROLE_VIEW_ALL,
            Product::ROLE_VIEW,
            Supplier::ROLE_VIEW,
            Supplier::ROLE_EDIT,
            Warehouse::ROLE_VIEW,
        ]);
        $em->persist($volunteer);
        $this->setReference('group_volunteer', $volunteer);

        $partner = new Group();
        $partner->setName('Partner');
        $partner->setRoles([
            Client::ROLE_MANAGE_OWN,
            Order::ROLE_MANAGE_OWN,
            Partner::ROLE_MANAGE_OWN,
            User::ROLE_PARTNER,
        ]);
        $em->persist($partner);
        $this->setReference('group_partner', $partner);

        $em->flush();
    }
}

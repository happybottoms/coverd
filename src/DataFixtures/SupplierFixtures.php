<?php

namespace App\DataFixtures;

use App\Entity\Supplier;
use App\Entity\SupplierAddress;
use App\Entity\SupplierContact;
use Doctrine\Persistence\ObjectManager;

class SupplierFixtures extends BaseFixture
{
    public function loadData(ObjectManager $manager)
    {
        for ($i = 0; $i < 50; $i++) {
            $supplier = new Supplier($this->faker->company);
            $supplier->addContact($this->createContact(SupplierContact::class));
            $supplier->addAddress($this->createAddress(SupplierAddress::class));
            $supplier->setSupplierType($this->faker->randomElement([
                Supplier::TYPE_DIAPERDRIVE,
                Supplier::TYPE_DONOR,
                Supplier::TYPE_DROPSITE,
                Supplier::TYPE_VENDOR
            ]));

            $manager->persist($supplier);
        }

        $manager->flush();
    }
}

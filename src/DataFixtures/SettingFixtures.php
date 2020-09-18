<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SettingFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function getDependencies()
    {
        return [
            ProductCategoryFixtures::class
        ];
    }

    public function loadData(ObjectManager $em)
    {
        $pullupCategory = $this->getReference('product_category_training_pants');

        $pullupCategorySetting = new Setting();
        $pullupCategorySetting->setConfig('pullupCategory');
        $pullupCategorySetting->setValue($pullupCategory->getId());
        $em->persist($pullupCategorySetting);

        $em->flush();
    }
}

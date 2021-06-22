<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Moment\Moment;

class SettingFixtures extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies()
    {
        return [
            ProductCategoryFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['MinimumFixtures'];
    }

    public function loadData(ObjectManager $em)
    {
        $pullupCategory = $this->getReference('product_category_training_pants');

        $this->config->create('zipCountyStates', ['MO', 'KS']);
        $this->config->create('pullupCategory', $pullupCategory->getId());

        $now = new Moment();

        $this->config->create('partnerReviewStart', $now->cloning()->addMonths(1)->format());
        $this->config->create('partnerReviewEnd', $now->cloning()->addMonths(2)->format());
        $this->config->create('partnerReviewLastStartRun', $now->cloning()->subtractMonths(2)->format());
        $this->config->create('partnerReviewLastEndRun', $now->cloning()->subtractMonths(1)->format());

        $this->config->create('clientReviewStart', $now->cloning()->addMonths(1)->format());
        $this->config->create('clientReviewEnd', $now->cloning()->addMonths(2)->format());
        $this->config->create('clientReviewLastStartRun', $now->cloning()->subtractMonths(2)->format());
        $this->config->create('clientReviewLastEndRun', $now->cloning()->subtractMonths(1)->format());
    }
}

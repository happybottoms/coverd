<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\EAV\ClientDefinition;
use App\Entity\Partner;
use App\Entity\ValueObjects\Name;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Workflow\Registry;

class ClientFixtures extends BaseFixture implements DependentFixtureInterface
{
    /** @var Registry */
    protected $workflowRegistry;

    public function __construct(Registry $workflowRegistry)
    {
        $this->workflowRegistry = $workflowRegistry;
    }

    public function getDependencies()
    {
        return [
            ClientAttributeFixtures::class,
            PartnerFixtures::class
        ];
    }

    protected function loadData(ObjectManager $manager)
    {
        $definitions = $manager->getRepository(ClientDefinition::class)->findAll();

        $statuses = [
            Client::STATUS_CREATION,
            Client::STATUS_ACTIVE,
            Client::STATUS_INACTIVE,
            Client::STATUS_LIMIT_REACHED,
            Client::STATUS_DUPLICATE_INACTIVE,
        ];


        foreach ($this->getData() as $clientArr) {
            $client = new Client($this->workflowRegistry);
            $client->setName($clientArr['name']);
            $client->setStatus($clientArr['status']);
            $client->setPartner($clientArr['partner']);
            $client->setBirthdate($this->faker->dateTimeBetween('-5 years', 'now'));

            foreach ($definitions as $definition) {
                $attribute = $definition->createAttribute();
                $attribute->setValue($attribute->fixtureData());
                $client->addAttribute($attribute);
            }


            $manager->persist($client);
        }

        $manager->flush();
    }

    /**
     * @return Client[]
     */
    private function getData(): array
    {
        $clientsToCreate = 500;
        $faker = Factory::create();

        $clients = [];

        for ($i = 0; $i < $clientsToCreate; $i++) {
            $status = $this->getClientStatus();
            $partner = null;
            if (
                $status === Client::STATUS_CREATION
                || $status === Client::STATUS_ACTIVE
                || $status === Client::STATUS_LIMIT_REACHED
            ) {
                $partner = $this->getActivePartner();
            } else {
                $partner = $this->getInactivePartner();
            }

            $clients[] = [
                'name' => new Name($faker->firstName, $faker->lastName),
                'status' => $status,
                'partner' => $partner,
            ];
        }

        return $clients;
    }

    private function getClientStatus(): string
    {
        $statuses = [
            Client::STATUS_ACTIVE,
            Client::STATUS_LIMIT_REACHED,
            Client::STATUS_CREATION,
            Client::STATUS_INACTIVE,
        ];

        return $this->randomArrayValue($statuses);
    }

    private function getActivePartner(): Partner
    {
        $partners = $this->manager
            ->getRepository(Partner::class)
            ->findBy(['status' => Partner::STATUS_ACTIVE]);

        return $this->randomArrayValue($partners);
    }

    private function getInactivePartner(): Partner
    {
        $partners = $this->manager
            ->getRepository(Partner::class)
            ->findBy(['status' =>
                [
                    Partner::STATUS_INACTIVE,
                    Partner::STATUS_START,
                    Partner::STATUS_APPLICATION_PENDING,
                    Partner::STATUS_APPLICATION_PENDING_PRIORITY,
                    Partner::STATUS_NEEDS_PROFILE_REVIEW,
                    Partner::STATUS_REVIEW_PAST_DUE,
                ]
            ]);

        return $this->randomArrayValue($partners);
    }

    private function randomArrayValue(array $arr)
    {
        return $arr[array_rand($arr)];
    }
}

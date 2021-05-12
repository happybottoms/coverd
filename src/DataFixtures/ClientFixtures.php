<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\EAV\Attribute;
use App\Entity\EAV\AttributeDefinition;
use App\Entity\EAV\ClientAttributeDefinition;
use App\Entity\Partner;
use App\Entity\ValueObjects\Name;
use App\IdGenerator\RandomIdGenerator;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Workflow\Registry;

class ClientFixtures extends BaseFixture implements DependentFixtureInterface
{
    /** @var Registry */
    protected $workflowRegistry;

    /** @var RandomIdGenerator */
    protected $gen;

    public function __construct(Registry $workflowRegistry, RandomIdGenerator $gen)
    {
        $this->workflowRegistry = $workflowRegistry;
        $this->gen = $gen;
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
        $definitions = $manager->getRepository(ClientAttributeDefinition::class)->findAll();

        $statuses = [
            Client::STATUS_ACTIVE,
            Client::STATUS_ACTIVE,
            Client::STATUS_ACTIVE,
            Client::STATUS_INACTIVE,
            Client::STATUS_INACTIVE_DUPLICATE,
        ];


        foreach ($this->getData() as $clientArr) {
            $client = new Client($this->workflowRegistry);
            $client->setName($clientArr['name']);
            $client->setStatus($clientArr['status']);
            $client->setParentFirstName($clientArr['parentFirstName']);
            $client->setParentLastName($clientArr['parentLastName']);
            $client->setPartner($clientArr['partner']);
            $client->setPublicId($this->gen->generate());
            $client->setBirthdate($this->faker->dateTimeBetween('-5 years', 'now'));

            foreach ($definitions as $definition) {
                $attribute = new Attribute($definition);
                if ($definition->getType() === AttributeDefinition::TYPE_ZIPCODE) {
                    $attribute->setValue($this->getReference('zip_county.1'));
                } else {
                    $attribute->populateFixture();
                }
                $client->addAttribute($attribute);
            }

            $manager->persist($client);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $clientsToCreate = 80;
        $faker = Factory::create();

        $clients = [];

        for ($i = 0; $i < $clientsToCreate; $i++) {
            $status = $this->getClientStatus();
            $partner = null;
            if ($status === Client::STATUS_INACTIVE && rand(0, 1)) {
                $partner = $this->getInactivePartner();
            } else {
                $partner = $this->getActivePartner();
            }

            $lastName = $faker->lastName;

            $clients[] = [
                'name' => new Name($faker->firstName, $lastName),
                'parentFirstName' => $faker->firstName,
                'parentLastName' => $faker->optional(.3, $lastName)->lastName,
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
            Client::STATUS_ACTIVE,
            Client::STATUS_ACTIVE,
            Client::STATUS_ACTIVE,
            Client::STATUS_INACTIVE_DUPLICATE,
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

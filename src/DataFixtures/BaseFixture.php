<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Contact;
use App\Entity\ValueObjects\Name;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

abstract class BaseFixture extends Fixture
{
    /** @var ObjectManager */
    protected $manager;

    /** @var Generator */
    protected $faker;

    abstract protected function loadData(ObjectManager $manager);

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->faker = Factory::create();

        $this->loadData($manager);
    }

    protected function createAddress($class): Address
    {
        /** @var Address $address */
        $address = new $class();
        $address->setStreet1($this->faker->streetAddress);
        $address->setCity($this->faker->city);
        $address->setState($this->faker->stateAbbr);
        $address->setPostalCode($this->faker->postcode);
        $address->setCountry($this->faker->country);

        return $address;
    }

    protected function createContact($class): Contact
    {
        /** @var Contact $contact */
        $contact = new $class();
        $contact->setName(new Name($this->faker->firstName, $this->faker->lastName));
        $contact->setTitle($this->faker->jobTitle);
        $contact->setEmail($this->faker->email);
        $contact->setPhoneNumber($this->faker->phoneNumber);

        return $contact;
    }

    protected function createMany(string $className, int $count, callable $factory)
    {
        for ($i = 0; $i < $count; $i++) {
            $entity = new $className();
            $factory($entity, $i);

            $this->manager->persist($entity);
            // store for usage later as App\Entity\ClassName_#COUNT#
            $this->addReference($className . '_' . $i, $entity);
        }
    }
}

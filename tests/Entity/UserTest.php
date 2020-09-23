<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\ValueObjects\Name;
use App\Tests\AbstractWebTestCase;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

class UserTest extends AbstractWebTestCase
{
    public function testSetPassword(UserPasswordEncoder $encoder): void
    {
        $faker = Factory::create();

        $user = new User($faker->email);
        $user->setName(Name::fromString($faker->name));

        $plainTextPassword = $faker->password;
        $user->setPlainTextPassword($plainTextPassword);
        $this->objectManager->persist($user);

        $this->assertNotEquals($plainTextPassword, $user->getPassword());
        $this->assertTrue($encoder->isPasswordValid($user, $plainTextPassword));

        // Ensure that the plainTextPassword is no longer set
        $this->assertNull($user->getPlainTextPassword());
    }
}

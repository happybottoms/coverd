<?php


namespace App\Tests\Entity;

use App\Entity\Client;
use App\Entity\ValueObjects\Name;
use App\Tests\AbstractWebTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

class ClientTest extends AbstractWebTestCase
{

    public function testConstructorGeneratesUuid()
    {
        $client      = new Client();
        $uuid        = $client->getUuid();
        $isValidUuid = Uuid::isValid($uuid);
        $this->assertTrue($isValidUuid, "UUID: {$uuid}");
    }

    public function testSetNameThrowsExceptionWhenInvalid()
    {
        $this->expectException(MissingMandatoryParametersException::class);
        $this->expectExceptionMessage('Missing first and/or last name');
        $name   = new Name();
        $client = new Client();
        $client->setName($name);
    }

}

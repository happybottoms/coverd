<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class AbstractWebTestCase extends WebTestCase
{
    /** @var ?KernelBrowser */
    protected $client;

    /** @var ObjectManager */
    protected $objectManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->objectManager = static::bootKernel()
            ->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->client = null;

        $this->objectManager->close();
        $this->objectManager = null; // avoid memory leaks
    }

    protected function loginAsAdmin(): void
    {
        $firewallName = 'main';
        $firewallContext = 'main';

        /** @var UserInterface $admin */
        $admin = $this->objectManager->getRepository(User::class)->findOneBy(['email' => 'admin@example.com']);

        $token = new PostAuthenticationGuardToken($admin, $firewallName, $admin->getRoles());

        $session = static::bootKernel()->getContainer()->get('session');
        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $this
            ->client
            ->getCookieJar()
            ->set(new Cookie($session->getName(), $session->getId()));
    }

    /**
     * @param bool $decodeAsArray
     * @return null|\stdClass|array
     */
    protected function getDecodedResponse(bool $decodeAsArray = true)
    {
        $response = $this->client->getResponse()->getContent();
        if (!$this->client->getResponse()->isOk()) {
            return null;
        }

        $decodedResponse = json_decode($response, $decodeAsArray);

        return $decodedResponse ? $decodedResponse['data'] : $decodedResponse->{'data'};
    }
}

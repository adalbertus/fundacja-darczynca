<?php

namespace App\Tests;

use App\DataFixtures\AppFixtures;
use App\Entity\User;
use App\Entity\Donor;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zenstruck\Foundry\Test\Factories;
use App\Factory\DonorFactory;
use LogicException;



class BaseWebTestCase extends WebTestCase
{
    use Factories;
    protected UrlGeneratorInterface $router;
    protected KernelBrowser $client;
    protected UserRepository $userRepository;
    protected $entityManager;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get('router');
        $this->userRepository = static::getContainer()->get(UserRepository::class);

        if ('test' !== self::$kernel->getEnvironment()) {
            throw new LogicException('Execution only in Test environment possible!');
        }
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * Otworzenie strony na podstawie nazwy i parametrów
     * @param string $routeName Route name
     * @param array $parameters
     * @return Crawler
     */
    protected function open(string $routeName, array $parameters = [], string $method = 'GET'): Crawler
    {
        return $this->client->request($method, $this->createUrl($routeName, $parameters));
    }

    /**
     * Tworzenie URL na podstawie nazwy i parametrów
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    protected function createUrl(string $routeName, array $parameters = []): string
    {
        return $this->router->generate($routeName, $parameters);
    }

    protected function assertRedirectsToLogin(string $message = "")
    {
        $loginUrl = $this->router->generate('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->assertResponseRedirects($loginUrl, 302, $message);
    }

    protected function assertAccessDenied(string $message = "")
    {
        $this->assertResponseStatusCodeSame(403, $message);
    }

    protected function loginAdmin()
    {
        $user = $this->userRepository->findOneByEmail(AppFixtures::ADMIN_EMAIL);
        $this->client->loginUser($user);
    }

    protected function loginUser(): User
    {
        $user = $this->userRepository->findOneByEmail(AppFixtures::USER_EMAIL);
        $this->client->loginUser($user);
        return $user;
    }

    protected function loginDonor(): User
    {
        $user = $this->userRepository->findOneByEmail(AppFixtures::DONOR_EMAIL);
        $this->client->loginUser($user);
        return $user;
    }

    protected function _createDonorWithUser(): Donor|\Zenstruck\Foundry\Proxy
    {
        $donor = DonorFactory::createOne();
        $user = $this->userRepository->findOneByEmail(AppFixtures::DONOR_EMAIL);
        $donor->setUser($user);
        $donor->save();
        return $donor;
    }
}
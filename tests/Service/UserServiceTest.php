<?php

namespace App\Tests\Service;

use App\Constants\UserRolesKeys;
use App\Entity\User;
use App\Factory\UserFactory;
use App\Service\UserService;
use App\Tests\DatabaseTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserServiceTest extends DatabaseTestCase
{
    use MailerAssertionsTrait;
    private UserService $userService;
    protected $entityManager;


    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = static::getContainer()->get(UserService::class);
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

    }

    public function testProperNumerOfUsersWillBeReturned()
    {
        $usersFactory = UserFactory::createMany(3);
        $userFactory = UserFactory::new()->withoutPersisting()->create();
        $existing = [];
        $new = [];
        foreach ($usersFactory as $user) {
            $existing[] = $user->object()->getEmail();
        }
        $new[] = $userFactory->object()->getEmail();

        $users = $this->userService->createUsersByEmailIfNeeded(implode(';', array_merge($existing, $new)));
        $existingUsers = array_filter($users, function ($row) {
            return !$row['new'];
        });
        $newUsers = array_filter($users, function ($row) {
            return $row['new'];
        });
        $this->assertCount(count($existing), $existingUsers);
        $this->assertCount(count($new), $newUsers);

        foreach ($existingUsers as $key => $user) {
            $this->assertContains($key, $existing, "Brakuje adresu {$key}");
        }

        foreach ($newUsers as $key => $user) {
            $this->assertContains($key, $new, "Brakuje adresu {$key}");
        }
    }

    public function testNewUserHasFirstAndLastName()
    {
        $users = $this->userService->createUsersByEmailIfNeeded(UserFactory::faker()->unique()->email(), UserRolesKeys::USER, true, 'Marian', 'Bąk');
        $user = current($users);
        $this->assertEquals($user['user']->getFirstName(), 'Marian');
        $this->assertEquals($user['user']->getLastName(), 'Bąk');
    }

    public function testExistingUsersAreOnlyFetchedAndNotCreated()
    {
        $usersFactory = UserFactory::createMany(3);
        $emails = [];
        foreach ($usersFactory as $user) {
            $emails[] = $user->object()->getEmail();
        }

        $users = $this->userService->createUsersByEmailIfNeeded(implode(';', $emails));

        foreach ($usersFactory as $user) {
            $this->assertEquals($user->object(), $users[$user->getEmail()]['user']);
        }
    }

    public function testUsersAreCreated()
    {
        $emails = [];
        $emails[] = UserFactory::faker()->email();
        $emails[] = UserFactory::faker()->email();

        $users = $this->userService->createUsersByEmailIfNeeded(implode(';', $emails));

        $this->assertCount(count($emails), $users);
        $newUsers = array_filter($users, function ($row) {
            return $row['new'];
        });
        foreach ($newUsers as $row) {
            $this->assertTrue($this->entityManager->contains($row['user']));
        }

        foreach ($emails as $email) {
            $usersFound = array_filter($users, function ($row) use ($email) {
                return $row['user']->getEmail() === $email;
            });
            $this->assertCount(1, $usersFound, "Nieznaleziono użytkownika o emailu $email.");
        }
    }
}
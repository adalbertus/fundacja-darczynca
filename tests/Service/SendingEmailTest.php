<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Factory\DonorFactory;
use App\Factory\MemberFactory;
use App\Factory\UserFactory;
use App\Service\DonorService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SendingEmailTest extends WebTestCase
{
    use MailerAssertionsTrait;
    private UserService $userService;
    private DonorService $donorService;
    protected $entityManager;
    private KernelBrowser $client;


    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->userService = static::getContainer()->get(UserService::class);
        $this->donorService = static::getContainer()->get(DonorService::class);
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

    }

    public function testSendNewUserNotificationEmail()
    {
        $this->client->request('GET', '/mail/send');
        // $this->assertResponseIsSuccessful();


        $user = UserFactory::createOne();
        $this->userService->setRandomPasswordAndSendNewAccountNotification($user->object());
        $user->save();
        $email = $this->getMailerMessage();

        $this->assertEmailCount(1);
        $this->assertEmailAddressContains($email, 'to', $user->object()->getEmail());
    }

    public function testUserWithLastLoginWillNotHaveNewPassword()
    {
        $this->client->request('GET', '/mail/send');
        // $this->assertResponseIsSuccessful();


        $user = UserFactory::createOne(['loginSuccess' => date_create(), 'password' => 'ala ma kota']);
        $this->userService->setRandomPasswordAndSendNewAccountNotification($user->object());
        $user->save();
        $email = $this->getMailerMessage();

        $this->assertEmailCount(0);
        $this->assertEquals('ala ma kota', $user->getPassword());
        // $this->assertEmailAddressContains($email, 'to', $user->object()->getEmail());

    }

    public function testNewDonoNotificationSendWithPasswordForNewUser()
    {
        $this->client->request('GET', '/mail/send');
        $user = UserFactory::new()->withoutPersisting()->create()->object();
        $donor = DonorFactory::createOne();
        $this->donorService->addOrRemoveUserBasedOnEmails($donor->object(), $user->getEmail());
        $email = $this->getMailerMessage();

        $this->assertEmailCount(1);
        $this->assertEmailAddressContains($email, 'to', $user->getEmail());
        $this->assertEmailHtmlBodyContains($email, 'email_template_new_donor_user_notification');
    }

    public function testNewDonorNotificationNotSendForExistingUser()
    {
        $this->client->request('GET', '/mail/send');

        $user = UserFactory::createOne(['loginSuccess' => date_create()]);
        $donor = DonorFactory::createOne();
        $this->donorService->addOrRemoveUserBasedOnEmails($donor->object(), $user->getEmail());

        $email = $this->getMailerMessage();
        $this->assertEmailCount(0);
    }
}
<?php

namespace App\Tests\Service;

use App\Constants\UserRolesKeys;
use App\Factory\DonorFactory;
use App\Factory\UserFactory;
use App\Repository\DonorRepository;
use App\Repository\UserRepository;
use App\Service\DonorService;
use App\Tests\DatabaseTestCase;
use App\Entity\Donor;
use App\Entity\User;
use App\Entity\BankHistory;
use App\Repository\BankHistoryRepository;
use App\Factory\BankHistoryFactory;
use Doctrine\ORM\UnitOfWork;



class DonorServiceTest extends DatabaseTestCase
{
    private DonorService $donorService;
    private DonorRepository $donorRepository;
    private UserRepository $userRepository;
    private BankHistoryRepository $bankHistoryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->donorService = static::getContainer()->get(DonorService::class);
        $this->donorRepository = $this->entityManager->getRepository(Donor::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->bankHistoryRepository = $this->entityManager->getRepository(BankHistory::class);
    }

    public function providerSenderNamesWithExpectedPatterns(): array
    {
        return [
            ["IRENA BOŻENA WĄSIK ELIXIR 11-10-2023", "irena\s?bozena\s?wasik\s?elixir\s?111020"],
            [' STEFAN          OGNISKO, UL.KOZIOŁKA 8D, 87-541 POZNAŃ ', 'stefan\s?ognisko,\s?ulkoziolka\s?8d,\s?8'],
            ['   TO-Jest_jakas      dziwna "nazwa"           OGNISKO, UL.KOZIOŁKA 8D, 87-541 POZNAŃ   ', 'tojestjakas\s?dziwna\s?"nazwa"\s?ognis']
        ];
    }
    /**
     * @dataProvider providerSenderNamesWithExpectedPatterns
     */
    public function testDonorIsCreatedBasedOnSenderName(string $senderName, string $expectedRegexpPattern): void
    {
        $donor = $this->donorService->createDonor($senderName);

        $this->assertEquals($donor->getName(), "Darczyńca nr 1", "senderName = {$senderName}], expectedRegexpPattern = {$expectedRegexpPattern}");
        $this->assertEquals($donor->isAuto(), true, "senderName = {$senderName}], expectedRegexpPattern = {$expectedRegexpPattern}");
        $this->assertCount(1, $donor->getDonorSearchPatterns(), "senderName = {$senderName}], expectedRegexpPattern = {$expectedRegexpPattern}");
        $this->assertEquals($expectedRegexpPattern, $donor->buildRegexpPattern(), "senderName = {$senderName}], expectedRegexpPattern = {$expectedRegexpPattern}");
    }

    public function testAddOrRemoveUserBasedOnEmails(): void
    {
        $donor = DonorFactory::createOne();

        $this->donorService->addOrRemoveUserBasedOnEmails($donor->object(), 'jan.kowalski@example.com');
        $donor->save();
        $user = $this->userRepository->findOneByEmail('jan.kowalski@example.com');

        $this->assertEquals($user, $donor->getUser());
        $this->assertTrue($user->hasRole(UserRolesKeys::DONOR));
    }

    public function testGetDonorByUserReturnsNotNull(): void
    {
        $user = UserFactory::createOne();
        $donor = DonorFactory::createOne([
            'user' => $user,
        ]);
        $donor->save();
        $donorFromDb = $this->donorService->getDonorByUser($user->object());

        $this->assertNotNull($donorFromDb);
        $this->assertEquals($donor->getId(), $donorFromDb->getId());
    }

    public function testGetDonorByUserReturnsNull(): void
    {
        $user = UserFactory::createOne();
        DonorFactory::createOne([
            'user' => UserFactory::createOne(),
        ]);
        $donorFromDb = $this->donorService->getDonorByUser($user->object());

        $this->assertNull($donorFromDb);
    }

    public function testDeleteAndTransferIfNeededWithTransactions(): void
    {
        $donor1 = DonorFactory::createOne();
        $donor1Id = $donor1->getId();
        $donor2 = DonorFactory::createOne();

        BankHistoryFactory::createMany(10, ['donor' => $donor1, 'is_draft' => false]);
        BankHistoryFactory::createMany(20, ['donor' => $donor2, 'is_draft' => false]);


        $countDonor2Before = $this->bankHistoryRepository->count([
            'donor' => $donor2->object()
        ]);

        $this->donorService->deleteAndTransferIfNeeded($donor1->object(), $donor2->object());

        $donor1Counter = $this->donorRepository->count(['id' => $donor1Id]);

        $countDonor2After = $this->bankHistoryRepository->count([
            'donor' => $donor2->object()
        ]);

        $this->assertEquals(0, $donor1Counter);
        $this->assertEquals(20, $countDonor2Before);
        $this->assertEquals(30, $countDonor2After);
        $this->assertCount(30, $donor2->getBankHistoryTransactions());
    }

    public function testDeleteAndTransferIfNeededNoTransactions(): void
    {
        $donor1 = DonorFactory::createOne();
        $donor1Id = $donor1->getId();
        $donor2 = DonorFactory::createOne();

        BankHistoryFactory::createMany(20, ['donor' => $donor2, 'is_draft' => false]);


        $countDonor2Before = $this->bankHistoryRepository->count([
            'donor' => $donor2->object()
        ]);

        $this->donorService->deleteAndTransferIfNeeded($donor1->object(), $donor2->object());

        $donor1Counter = $this->donorRepository->count(['id' => $donor1Id]);

        $countDonor2After = $this->bankHistoryRepository->count([
            'donor' => $donor2->object()
        ]);

        $this->assertEquals(0, $donor1Counter);
        $this->assertEquals(20, $countDonor2Before);
        $this->assertEquals(20, $countDonor2After);
        $this->assertCount(20, $donor2->getBankHistoryTransactions());
    }

    public function testDeleteAndTransferIfNeededNoSecondDonor(): void
    {
        $donor1 = DonorFactory::createOne();
        $donor1Id = $donor1->getId();

        $this->donorService->deleteAndTransferIfNeeded($donor1->object(), null);

        $donor1Counter = $this->donorRepository->count(['id' => $donor1Id]);
        $this->assertEquals(0, $donor1Counter);
    }
}
<?php

namespace App\Tests\Service;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use App\Entity\Donor;
use App\Factory\BankHistoryFactory;
use App\Factory\DonorFactory;
use App\Repository\BankHistoryRepository;
use App\Repository\DonorRepository;
use App\Service\BankHistoryImportService;
use App\Service\DonorService;
use App\Tests\DatabaseTestCase;

class BankHistoryImportServiceTest extends DatabaseTestCase
{
    private BankHistoryImportService $bankHistoryImport;
    private DonorService $donorService;
    private BankHistoryRepository $bankHistoryRepository;
    private DonorRepository $donorRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bankHistoryImport = static::getContainer()->get(BankHistoryImportService::class);
        $this->donorService = static::getContainer()->get(DonorService::class);
        $this->bankHistoryRepository = $this->entityManager->getRepository(BankHistory::class);
        $this->donorRepository = $this->entityManager->getRepository(Donor::class);
    }


    public function testBankHistoryCleanOldData(): void
    {
        BankHistoryFactory::createMany(50, ['is_draft' => true]);
        BankHistoryFactory::createMany(75, ['is_draft' => false]);
        $all = $this->bankHistoryRepository->findAll();


        $this->bankHistoryImport->cleanOldData();
        $resultDraft = $this->bankHistoryRepository->findBy(['is_draft' => true]);
        $resultNotDraft = $this->bankHistoryRepository->findBy(['is_draft' => false]);

        $this->assertCount(75, $resultNotDraft);
        $this->assertCount(0, $resultDraft);
        $this->assertCount(125, $all);
    }

    public function testSaveOnlyUniqueCsv(): void
    {

        $bankHistoryList = BankHistoryFactory::createMany(10, ['is_draft' => false]);

        // bh1, bh2 się powtarzają (to samo raw)

        $bh1 = clone $bankHistoryList[0]->object();
        $bh2 = clone $bankHistoryList[1]->object();
        $bh3 = BankHistoryFactory::new()->withoutPersisting()->create()->object();
        $bh4 = BankHistoryFactory::new()->withoutPersisting()->create()->object();

        $this->bankHistoryImport->saveOnlyUniqueCsv([$bh1, $bh2, $bh3, $bh4]);
        $all = $this->bankHistoryRepository->findAll();

        $this->assertCount(12, $all);
    }


    public function testImportingBankHistoryWillCleanOldDataFirst(): void
    {
        BankHistoryFactory::createMany(50, ['is_draft' => true]);
        BankHistoryFactory::createMany(30, ['is_draft' => false]);

        $this->bankHistoryImport->cleanOldAndImportUniqueFromCSV(self::getFullPath("bank_history_santander_v1_4rows.csv"));
        $all = $this->bankHistoryRepository->findAll();
        $draft = $this->bankHistoryRepository->findBy(['is_draft' => true]);
        $notDraft = $this->bankHistoryRepository->findBy(['is_draft' => false]);

        $this->assertCount(34, $all);
        $this->assertCount(4, $draft, "Draft powinno być tylko 12");
        $this->assertCount(30, $notDraft);
    }

    private function createTestDonors(): void
    {
        $this->donorService->createDonor('TOMASZ BĄK UL. WALDKA 23 50-323 DOLNY BRZEG ELIXIR 27-10-2023');
        $this->donorService->createDonor('IRENA BOŻENA WĄSIK ELIXIR 11-10-2023');
        $this->donorService->createDonor('MARZENA ELEONORA PAWELEC UL. ŻWIRKI 27 92-314 WARSZAWA');
        $this->donorService->createDonor('Tomasz Garguń, ul. Macedońska 71A, 82-312 Piotrków Trybunalski');
        $this->donorService->createDonor(' STEFAN          OGNISKO, UL.KOZIOŁKA 8D, 87-541 POZNAŃ ');
        $this->donorService->createDonor('   TO-Jest_jakas      dziwna "nazwa"           OGNISKO, UL.KOZIOŁKA 8D, 87-541 POZNAŃ   ');
    }

    public function providerSenderName(): array
    {
        return [
            ['Darczyńca nr 1', 'TOMASZ BĄK UL. WALDKA 23 50-323 DOLNY BRZEG ELIXIR 27-10-2023'],
            ['Darczyńca nr 2', 'IRENA BOŻENA WĄSIK ELIXIR 11-10-2023'],
            ['Darczyńca nr 3', 'MARZENA ELEONORA PAWELEC UL. ŻWIRKI 27 92-314 WARSZAWA'],
            ['Darczyńca nr 4', 'Tomasz Garguń, ul. Macedońska 71A, 82-312 Piotrków Trybunalski'],
            ['Darczyńca nr 5', ' STEFAN          OGNISKO, UL.KOZIOŁKA 8D, 87-541 POZNAŃ '],
            ['Darczyńca nr 6', '   TO-Jest_jakas      dziwna "nazwa"           OGNISKO, UL.KOZIOŁKA 8D, 87-541 POZNAŃ   '],
        ];
    }

    /**
     * @dataProvider providerSenderName
     */
    public function testIdentyfyExistingDonor($expectedDarczynca, $senderName): void
    {
        $this->createTestDonors();
        $donor = $this->bankHistoryImport->identyfyDonor($senderName);

        $this->assertIsNotBool($donor, "expected[{$expectedDarczynca}] senderName[{$senderName}]");
        $this->assertEquals($expectedDarczynca, $donor->getName(), "expected[{$expectedDarczynca}] senderName[{$senderName}]");
    }

    /**
     * @dataProvider providerSenderName
     */
    public function testIdentyfyDonorCreateOneIfNotExists($expectedDarczynca, $senderName): void
    {
        // uwaga ignoruję $expectedDarczynca - ponieważ tutaj zawsze powinno to wynosić Darczynca nr 2

        DonorFactory::createOne(['name' => 'Darczyńca nr 1', 'is_auto' => true]);
        DonorFactory::createMany(5);
        $donor = $this->bankHistoryImport->identyfyDonor($senderName);

        $this->assertIsNotBool($donor, "senderName[{$senderName}]");
        $this->assertTrue($donor->isAuto(), "senderName[{$senderName}]");
        $this->assertEquals("Darczyńca nr 2", $donor->getName(), "senderName[{$senderName}]");
    }

    public function testIdentyfyDonorNonExistingIsAddedToCacheAfterCreation(): void
    {
        DonorFactory::createOne(['name' => 'Darczyńca nr 1', 'is_auto' => true]);
        DonorFactory::createMany(5);
        $cachedDonors = $this->bankHistoryImport->_getCachedData(Donor::class);
        $countBefore = count($cachedDonors);

        $senderName1 = "Koziołek Matołek";
        $donor1 = $this->bankHistoryImport->identyfyDonor($senderName1);

        $senderName2 = "Bolek i Lolek";
        $donor2 = $this->bankHistoryImport->identyfyDonor($senderName2);

        $cachedDonors = $this->bankHistoryImport->_getCachedData(Donor::class);

        $this->assertContains($donor1, $cachedDonors);
        $this->assertContains($donor2, $cachedDonors);
        $this->assertGreaterThan($countBefore, count($cachedDonors));
        $this->assertCount(8, $cachedDonors);
    }

    public function testAnalyzeIportedRowsWithDonorsResultsWithProperStates(): void
    {
        BankHistoryFactory::createMany(
            2,
            [
                'is_draft' => true,
                'category' => CategoryKeys::BRAK,
                'description' => 'DAROWIZNA NA CELE STATUTOWE',
            ]
        );
        $this->bankHistoryImport->analyzeAndSave();

        $bankHistoryList = $this->bankHistoryRepository->findAll();

        $this->assertCount(2, $bankHistoryList);
        $this->assertEquals(CategoryKeys::DAROWIZNA, $bankHistoryList[0]->getCategory());
        $this->assertNotNull($bankHistoryList[0]->getDonor());
        $this->assertEquals(CategoryKeys::DAROWIZNA, $bankHistoryList[1]->getCategory());
        $this->assertNotNull($bankHistoryList[1]->getDonor());
    }

    public function testAnalyzeIportedRowsWithOnlyOneDonor(): void
    {
        BankHistoryFactory::createMany(
            2,
            [
                'is_draft' => true,
                'category' => CategoryKeys::BRAK,
            ]
        );
        BankHistoryFactory::createOne(
            [
                'is_draft' => true,
                'category' => CategoryKeys::BRAK,
                'description' => 'DAROWIZNA NA CELE STATUTOWE',
            ]
        );
        $this->bankHistoryImport->analyzeAndSave();

        $bankHistoryList = $this->bankHistoryRepository->findAll();

        $this->assertCount(3, $bankHistoryList);
        $this->assertEquals(CategoryKeys::BRAK, $bankHistoryList[0]->getCategory());
        $this->assertNull($bankHistoryList[0]->getDonor());
        $this->assertEquals(CategoryKeys::BRAK, $bankHistoryList[1]->getCategory());
        $this->assertNull($bankHistoryList[1]->getDonor());
        $this->assertEquals(CategoryKeys::DAROWIZNA, $bankHistoryList[2]->getCategory());
        $this->assertNotNull($bankHistoryList[2]->getDonor());
    }

    public function testDraftBankHistoryIsSetFalseAfterAccept(): void
    {
        BankHistoryFactory::createMany(10, ['is_draft' => true]);
        $this->bankHistoryImport->acceptAllDraft();
        $draftCount = $this->bankHistoryRepository->count(['is_draft' => true]);
        $notDraftCount = $this->bankHistoryRepository->count(['is_draft' => false]);

        $this->assertEquals(0, $draftCount);
        $this->assertEquals(10, $notDraftCount);
    }

    public function testNotUsedAutoDonorsAreRemovedAfterAccept(): void
    {
        BankHistoryFactory::createMany(
            5,
            [
                'is_draft' => true,
                'category' => CategoryKeys::BRAK,
            ]
        );
        BankHistoryFactory::createMany(
            5,
            [
                'is_draft' => true,
                'category' => CategoryKeys::BRAK,
                'description' => 'DAROWIZNA NA CELE STATUTOWE',
            ]
        );
        $this->donorService->createDonor('Koziołek Matołek 1');
        $this->donorService->createDonor('Koziołek Matołek 2');
        $this->donorService->createDonor('Koziołek Matołek 3');
        $this->bankHistoryImport->analyzeAndSave();

        $this->bankHistoryImport->acceptAllDraft();

        $donorCount = $this->donorRepository->count([]);

        $this->assertEquals(5, $donorCount);
    }
}
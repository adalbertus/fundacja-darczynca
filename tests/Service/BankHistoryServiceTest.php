<?php

namespace App\Tests\Service;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use App\Factory\BankHistoryFactory;
use App\Factory\DonorFactory;
use App\Service\BankHistoryService;
use App\Repository\BankHistoryRepository;
use App\Tests\DatabaseTestCase;

class BankHistoryServiceTest extends DatabaseTestCase
{
    private BankHistoryService $bankHistoryService;
    private BankHistoryRepository $bankHistoryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bankHistoryService = static::getContainer()->get(BankHistoryService::class);
        $this->bankHistoryRepository = $this->entityManager->getRepository(BankHistory::class);
    }

    private function _createDonorExampleData(): array
    {
        $donor1 = DonorFactory::createOne();
        $donor2 = DonorFactory::createOne();

        BankHistoryFactory::createMany(5, [
            'is_draft' => true
        ]);
        BankHistoryFactory::createMany(5, [
            'is_draft' => true,
            'category' => CategoryKeys::DAROWIZNA,
            'donor' => $donor1,
        ]);
        BankHistoryFactory::createMany(5, [
            'is_draft' => false,
            'category' => CategoryKeys::DAROWIZNA,
            'donor' => $donor2,
        ]);
        BankHistoryFactory::createMany(5, [
            'is_draft' => false,
            'category' => CategoryKeys::DAROWIZNA,
            'donor' => $donor1,
        ]);
        BankHistoryFactory::createMany(5, [
            'is_draft' => false,
        ]);

        return [$donor1, $donor2];
    }

    public function testGetLastDonorTransactionsGets5(): void
    {
        $donors = $this->_createDonorExampleData();

        $result = $this->bankHistoryService->getLastDonorTransactions($donors[0]->object());

        $this->assertCount(5, $result);
        foreach ($result as $row) {
            $this->assertEquals($donors[0]->object(), $row->getDonor());
        }
    }

    public function testGetPagerQueryBuilderForDonor(): void
    {
        $donors = $this->_createDonorExampleData();

        $qb = $this->bankHistoryService->getPagerQueryBuilderForDonor($donors[0]->object());
        $result = $qb->getQuery()->execute();

        foreach ($result as $row) {
            $this->assertEquals($donors[0]->object(), $row->getDonor());
        }
    }
}
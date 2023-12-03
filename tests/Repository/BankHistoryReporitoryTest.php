<?php

namespace App\Tests\Repository;

use App\Entity\BankHistory;
use App\Factory\BankHistoryFactory;
use App\Factory\DonorFactory;
use App\Repository\BankHistoryRepository;
use App\Service\DateTimeHelper;
use App\Tests\DatabaseTestCase;

class BankHistoryReporitoryTest extends DatabaseTestCase
{
    private BankHistoryRepository $bankHistoryRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bankHistoryRepository = $this->entityManager->getRepository(BankHistory::class);
    }

    public function testGetSummary()
    {
        BankHistoryFactory::createMany(5, ['is_draft' => false]);

        $result = $this->bankHistoryRepository->getTotals();
        $total = $this->bankHistoryRepository->createQueryBuilder('bh')
            ->select('sum(bh.value)')
            ->andWhere('bh.is_draft = :is_draft')->setParameter(':is_draft', false)
            ->getQuery()->getSingleScalarResult();

        $updated = $this->bankHistoryRepository->createQueryBuilder('bh')
            ->select('max(bh.date)')
            ->andWhere('bh.is_draft = :is_draft')->setParameter(':is_draft', false)
            ->getQuery()->getSingleScalarResult();


        $this->assertEquals($total, $result['total']);
        $this->assertEquals($updated, $result['updated']);
    }

    public function testGetTotalsForDonor()
    {
        $donor1 = DonorFactory::createOne();
        $donor2 = DonorFactory::createOne();

        for ($idx = 1; $idx < 6; $idx++) {
            $curYear = date("Y");
            $prevYear = $curYear - 1;
            BankHistoryFactory::createOne([
                'is_draft' => false,
                'donor' => $donor1,
                'value' => 100,
                'date' => DateTimeHelper::createDateFromString("{$curYear}-0{$idx}-10"),
            ]);
            BankHistoryFactory::createOne([
                'is_draft' => false,
                'donor' => $donor1,
                'value' => 33,
                'date' => DateTimeHelper::createDateFromString("{$prevYear}-0{$idx}-04"),
            ]);
        }
        BankHistoryFactory::createMany(
            15,
            [
                'is_draft' => false,
                'donor' => $donor2
            ]
        );

        $result = $this->bankHistoryRepository->getTotalsForDonor($donor1->object());

        //'total' => 0, 'prev_year' => 0, 'cur_year' => 0, 'last5'
        $this->assertEquals(665, $result['total']);
        $this->assertEquals(165, $result['prev_year']);
        $this->assertEquals(500, $result['cur_year']);

    }

    public function testGetLastHistory()
    {
        BankHistoryFactory::createMany(50);
        $r = $this->bankHistoryRepository->getLastHistory([
        ]);

        $this->assertCount(5, $r);
    }

}
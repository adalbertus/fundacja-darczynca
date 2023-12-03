<?php

namespace App\Tests\Service;

use App\Constants\CategoryKeys;
use App\Factory\BankHistoryFactory;
use App\Factory\DonorFactory;
use App\Service\DateTimeHelper;
use App\Service\SummaryService;
use App\Tests\DatabaseTestCase;

class SummaryServiceTest extends DatabaseTestCase
{
    private SummaryService $summaryService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->summaryService = static::getContainer()->get(SummaryService::class);
    }

    public function testGetTotalsIgnoresDraft(): void
    {
        BankHistoryFactory::createOne(
            [
                'value' => 100,
                'date' => DateTimeHelper::createDateFromString('2023-11-08'),
                'is_draft' => false,
            ]
        );
        BankHistoryFactory::createOne(
            [
                'value' => 300,
                'date' => DateTimeHelper::createDateFromString('2023-11-08'),
                'is_draft' => false,
            ]
        );

        BankHistoryFactory::createMany(10, ['is_draft' => true]);

        $summary = $this->summaryService->getTotals();

        $this->assertEquals("400.00", $summary['total']);
        $this->assertEquals('2023-11-08', $summary['updated']);
        $this->assertCount(2, $summary['last5']);
    }


    public function testGetTotalsForDonor(): void
    {
        $donor1 = DonorFactory::createOne();
        $donor2 = DonorFactory::createOne();

        BankHistoryFactory::createMany(10, [
            'is_draft' => false,
            'donor' => $donor1,
            'date' => BankHistoryFactory::faker()->dateTimeBetween('-5 years'),
        ]);
        BankHistoryFactory::createMany(10, [
            'is_draft' => false,
            'donor' => $donor2
        ]);
        $curYear = date("Y");
        for ($idx = 1; $idx < 6; $idx++) {
            BankHistoryFactory::createOne([
                'is_draft' => false,
                'donor' => $donor1,
                'value' => 100,
                'date' => DateTimeHelper::createDateFromString("{$curYear}-0{$idx}-10"),
            ]);
        }

        $result = $this->summaryService->getTotalsForDonor($donor1->object());

        $this->assertCount(5, $result['last5']);
        foreach ($result['last5'] as $row) {
            $rowDate = DateTimeHelper::format($row->getDate(), 'Y');
            $this->assertEquals(100, $row->getValue());
            $this->assertEquals($curYear, $rowDate);
        }
    }
}
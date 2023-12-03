<?php

namespace App\Tests\Service;

use App\Exception\InvalidCsvFileException;
use App\Service\SupportedBankFormat;
use App\Tests\DatabaseTestCase;
use App\Service\BankHistoryCsvReaderService;

class BankHistoryCsvReaderServiceTest extends DatabaseTestCase
{
    private BankHistoryCsvReaderService $bankHistoryCsvReader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bankHistoryCsvReader = static::getContainer()->get(BankHistoryCsvReaderService::class);
    }

    public function testParseCsvWithOnlyHeaderReturnsEmptyArray(): void
    {
        $bankHistories = $this->bankHistoryCsvReader->parse(self::getFullPath("bank_history_0_row.csv"), SupportedBankFormat::BankPekao);

        $this->assertCount(0, $bankHistories);
    }


    public function testParseINGBankCsvReturnsArrayWit5Rows(): void
    {
        $bankHistories = $this->bankHistoryCsvReader->parse(self::getFullPath("bank_history_ing_v1_5rows.csv"), SupportedBankFormat::INGBank);

        $this->assertCount(5, $bankHistories);
    }

    public function testParseSantanderCsvReturnsArrayWit5Rows(): void
    {
        $bankHistories = $this->bankHistoryCsvReader->parse(self::getFullPath("bank_history_santander_v1_4rows.csv"), SupportedBankFormat::SantanderBank);

        $this->assertCount(4, $bankHistories);
    }

    public function testParseInvalidCsvThrowException(): void
    {
        $this->expectException(InvalidCsvFileException::class);

        $this->bankHistoryCsvReader->parse(self::getFullPath("bank_history_invalid_1.csv"), SupportedBankFormat::BankPekao);
    }

    public function testCsvFilePassesBasicValidataion(): void
    {
        $bankHistories = $this->bankHistoryCsvReader->parse(self::getFullPath("bank_history_1_row.csv"), SupportedBankFormat::BankPekao);
        $bh = $bankHistories[0];

        $this->assertEquals('2020-02-21', $bh->getDate()->format('Y-m-d'));
        $this->assertEquals(1200.2, $bh->getValue());
        $this->assertEquals('OR2 13.11.2020 ŁĄCZKA SIWA KOWALSKA K', $bh->getDescription());
        $this->assertEquals('ZOFIA KOWALSKA', $bh->getSenderName());
        $this->assertEquals('10105000997603123456789123', $bh->getSenderBankAccount());
    }


    public function testMd5IsTheSameForWiteSpace()
    {
        $sampleRawRow1 = ['2023-03-01', '200.00', 'ANNA BARGIEŁA ', 'Triduum 2023 Kudowa- Zdrój Bargieła Anna  ', '17706500020652080797040001', '25124015741111001090648325'];
        $sampleRawRow2 = ['2023-03-01', '200.00', 'ANNA BARGIEŁA        ', 'Triduum 2023 Kudowa- Zdrój Bargieła Anna', '17706500020652080797040001', '25124015741111001090648325'];
        $raw1 = $this->bankHistoryCsvReader->createNormalizedRaw($sampleRawRow1);
        $raw2 = $this->bankHistoryCsvReader->createNormalizedRaw($sampleRawRow2);

        $this->assertEquals($raw1, $raw2);
    }
}
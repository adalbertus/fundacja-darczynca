<?php

namespace App\Service;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use App\Exception\UnrecognizedBankFormatException;
use App\Service\CsvParser\CsvColumns;
use App\Service\CsvParser\CsvParser;
use App\Service\CsvParser\INGBankCsvParser;
use App\Service\CsvParser\BankPekaoCsvParser;
use App\Service\CsvParser\SantanderBankCsvParser;
use App\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;


enum SupportedBankFormat: string
{
    case BankPekao = "BankPekao";
    case INGBank = 'INGBank';
    case SantanderBank = 'SantanderBank';
}
;

class BankHistoryCsvReaderService
{
    private ContainerInterface $container;
    private CsvParser $csvParser;


    public function __construct(
        private Kernel $kernel
    ) {
        $this->container = $kernel->getContainer();
    }

    /**
     * @return BankHistory[]
     */
    public function parse(string $filename, $bankFormat = SupportedBankFormat::INGBank): BankHistory|array
    {
        $csvParser = $this->getCsvParser($bankFormat);
        $rows = $csvParser->parse($filename);
        $bankHistoryList = [];
        foreach ($rows as $row) {
            $bh = BankHistory::create()
                ->setRaw($row[CsvColumns::RAW])
                ->setDate($row[CsvColumns::DATA])
                ->setValue($row[CsvColumns::KWOTA])
                ->setSenderName($row[CsvColumns::KONTRAHENT])
                ->setDescription($row[CsvColumns::TYTUL])
                ->setSenderBankAccount($row[CsvColumns::NR_RACHUNKU])
                ->setCategory(CategoryKeys::BRAK)
                ->setSubCategory(CategoryKeys::BRAK);
            $bankHistoryList[] = $bh;
        }

        return $bankHistoryList;
    }

    private function getCsvParser(SupportedBankFormat $bankFormat): CsvParser
    {
        return match ($bankFormat) {
            SupportedBankFormat::BankPekao => $this->container->get(BankPekaoCsvParser::class),
            SupportedBankFormat::INGBank => $this->container->get(INGBankCsvParser::class),
            SupportedBankFormat::SantanderBank => $this->container->get(SantanderBankCsvParser::class),
            default => throw new UnrecognizedBankFormatException('Wybrano nieobsługiwany format pliku CSV.')
        };
    }

    public function createNormalizedRaw($row)
    {
        $raw = [];
        foreach ($row as $col) {
            $raw[] = StringHelper::trimAll($col);
        }
        return implode(';', $raw);
    }
}
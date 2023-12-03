<?php
namespace App\Service\CsvParser;

use App\Service\StringHelper;

/**
 * Przykładowy plik CSV z Santander Bank zawiera takie kolumny:
2023-10-30,26-10-2023,'24 2193 3328 1111 2103 7629 6725,"NAZWA FUNDACJI UL. BIAŁA 16, 12-345 SZCZECIN",PLN,"1000,64","22321,20",4,
27-10-2023,27-10-2023,DAROWIZNA WPŁATA NA CELE STATUTOWENAZWA FUNDACJI,TOMASZ BĄK UL. WALDKA 23 50-323 DOLNY BRZEG ELIXIR 27-10-2023,36 1050 0002 9682 8351 9494 3924,"30,00","3332,30",1,
 * 
 */
class SantanderBankCsvParser extends CsvParser
{

    public const COL_DATA_KSIEGOWANIA = 0;
    public const COL_KWOTA = 5;
    public const COL_KONTRAHENT = 3;
    public const COL_TYTUL = 2;
    public const COL_RACHUNEK_KONTRAHENTA = 4;
    public const DATA_OFFSET = 2;
    public const NUMBER_OF_COLUMNS = 9;

    protected function getDataOffset(): int
    {
        return self::DATA_OFFSET;
    }
    protected function getNumberOfColumns(): int
    {
        return self::NUMBER_OF_COLUMNS;
    }

    protected function perfomCharsetConvertion(string $line): string
    {
        return $line;
    }

    protected function parseCsvIntoArray(string $rawLine): array
    {
        return str_getcsv($rawLine);
    }

    protected function processRow(array $csvRow): array
    {
        $bankAccount = StringHelper::trimAll($csvRow[self::COL_RACHUNEK_KONTRAHENTA], false, " \n\r\t\v\x00'");
        $row = [
            CsvColumns::DATA => \DateTime::createFromFormat('d-m-Y', StringHelper::trimAll($csvRow[self::COL_DATA_KSIEGOWANIA])),
            CsvColumns::KWOTA => floatval(StringHelper::trimAll($csvRow[self::COL_KWOTA])),
            CsvColumns::KONTRAHENT => StringHelper::trimAll($csvRow[self::COL_KONTRAHENT]),
            CsvColumns::TYTUL => StringHelper::trimAll($csvRow[self::COL_TYTUL]),
            CsvColumns::NR_RACHUNKU => $bankAccount
        ];
        return $row;
    }
}
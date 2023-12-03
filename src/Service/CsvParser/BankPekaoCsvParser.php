<?php
namespace App\Service\CsvParser;

use App\Service\StringHelper;

/**
 * Przykładowy plik CSV z BankPekao zawiera takie kolumny:
"Data transakcji";"Data księgowania";"Dane kontrahenta";"Tytuł";"Nr rachunku";"Nazwa banku";"Szczegóły";"Nr transakcji";"Kwota transakcji (waluta rachunku)";"Waluta";"Kwota blokady/zwolnienie blokady";"Waluta";"Kwota płatności w walucie";"Waluta";"Saldo po transakcji";"Waluta";;;;;
 * 
 */
class BankPekaoCsvParser extends CsvParser
{

    public const COL_DATA_KSIEGOWANIA = 0;
    public const COL_KWOTA = 1;
    public const COL_KONTRAHENT = 2;
    public const COL_TYTUL = 3;
    public const COL_RACHUNEK_KONTRAHENTA = 4;
    public const DATA_OFFSET = 2;
    public const NUMBER_OF_COLUMNS = 6;

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
        return str_getcsv($rawLine, ";");
    }

    protected function processRow(array $csvRow): array
    {
        $bankAccount = StringHelper::trimAll($csvRow[self::COL_RACHUNEK_KONTRAHENTA], false, " \n\r\t\v\x00'");
        $row = [
            CsvColumns::DATA => \DateTime::createFromFormat('Y-m-d', StringHelper::trimAll($csvRow[self::COL_DATA_KSIEGOWANIA])),
            CsvColumns::KWOTA => floatval(StringHelper::trimAll($csvRow[self::COL_KWOTA])),
            CsvColumns::KONTRAHENT => StringHelper::trimAll($csvRow[self::COL_KONTRAHENT]),
            CsvColumns::TYTUL => StringHelper::trimAll($csvRow[self::COL_TYTUL]),
            CsvColumns::NR_RACHUNKU => $bankAccount
        ];
        return $row;
    }

}
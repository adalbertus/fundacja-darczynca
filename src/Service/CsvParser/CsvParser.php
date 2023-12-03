<?php

namespace App\Service\CsvParser;

use App\Exception\InvalidCsvFileException;
use App\Service\StringHelper;
use TypeError;

class CsvColumns
{
    public const RAW = 'raw';
    public const DATA = 'data';
    public const KWOTA = 'kwota';
    public const KONTRAHENT = 'kontrahent';
    public const TYTUL = 'tytul';
    public const NR_RACHUNKU = 'nr_rachunku';

}

abstract class CsvParser
{
    abstract protected function getDataOffset(): int;
    abstract protected function getNumberOfColumns(): int;

    abstract protected function perfomCharsetConvertion(string $line): string;

    abstract protected function parseCsvIntoArray(string $rawLine): array;

    public function parse(string $filename): array
    {
        $rows = [];
        try {
            $rowIndex = 1;
            if (($handle = fopen($filename, "r")) !== FALSE) {
                while (($rawLine = fgets($handle)) !== false) {
                    if ($rowIndex++ < $this->getDataOffset()) {
                        continue;
                    }

                    $rawLine = $this->perfomCharsetConvertion($rawLine);

                    if (StringHelper::isNullOrEmpty(trim($rawLine))) {
                        continue;
                    }

                    $csvRow = $this->parseCsvIntoArray($rawLine);

                    if (count($csvRow) != $this->getNumberOfColumns()) {
                        continue;
                    }

                    $row = $this->processRow($csvRow);
                    $row[CsvColumns::RAW] = $rawLine;
                    $rows[] = $row;

                }
                fclose($handle);
                if ($rowIndex > $this->getDataOffset() && empty($rows)) {
                    throw new InvalidCsvFileException('Błędny format pliku CSV.');
                }
            }
        } catch (TypeError $te) {
            throw new InvalidCsvFileException($te->__toString());
        } catch (\Exception $e) {
            throw new InvalidCsvFileException($e->__toString());
        }
        return $rows;
    }

    abstract protected function processRow(array $csvRow): array;

}
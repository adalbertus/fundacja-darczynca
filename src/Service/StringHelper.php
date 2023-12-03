<?php

namespace App\Service;

class StringHelper
{
    public static function isNullOrEmpty(mixed $str): bool
    {
        return ($str === null || trim($str) === '');
    }

    public static function normalize(string $description): string
    {
        $result = self::trimAll($description);

        $result = str_replace('.', '', $result);
        $result = str_replace('_', '', $result);
        $result = str_replace('-', '', $result);
        $result = str_replace('/', '', $result);

        // usuwanie polskich znaczków
        $result = self::stripAccents($result);

        return strtolower($result);
    }

    public static function normalizeRegexPattern($pattern): string
    {
        $result = self::stripAccents($pattern);
        $result = str_replace('_', '', $result);
        $result = str_replace('-', '', $result);
        $result = str_replace('/', '', $result);
        $result = preg_replace('/\s+/', ' ', $result);
        $result = trim($result);

        // zamiana wyrażania 5. na
        $result = preg_replace('/(\d)\./', '$1', $result);
        // jak już wszystkie podwójne spacje są wycięte, to zamieniam pojedyncze spacje na \s?
        $result = str_replace(' ', '\s?', $result);

        return strtolower($result);
    }

    public static function stripAccents($text)
    {
        $replacement = array(
            '/ę/' => 'e',
            '/Ę/' => 'E',
            '/ó/' => 'o',
            '/Ó/' => 'O',
            '/ą/' => 'a',
            '/Ą/' => 'A',
            '/ś/' => 's',
            '/Ś/' => 'S',
            '/ł/' => 'l',
            '/Ł/' => 'L',
            '/ż/' => 'z',
            '/Ż/' => 'Z',
            '/ź/' => 'z',
            '/Ź/' => 'Z',
            '/ć/' => 'c',
            '/Ć/' => 'C',
            '/ń/' => 'n',
            '/Ń/' => 'N',
        );
        return preg_replace(array_keys($replacement), array_values($replacement), $text);
    }

    /**
     * Usunięcie znaków ($characters) na początku oraz końcu oraz zamiana wielokrotnych spacji
     * na jedną lub usunięcie ich całkowite. 
     *
     * @param  mixed $text
     * @param  mixed $removeAllSpaces - całkowite usunięcie lub nie sapcji 
     * @param  mixed $characters - jakie znaki należy przyciąć na początku i końcu $text
     * @return string
     */
    public static function trimAll($text, bool $removeAllSpaces = false, string $characters = " \n\r\t\v\x00"): string
    {
        if ($removeAllSpaces) {
            $result = preg_replace('/\s+/', '', trim($text, $characters));
        } else {
            $result = preg_replace('/\s+/', ' ', trim($text, $characters));
        }
        return $result;
    }

    public static function insertStringBetweenCharacters($text, $stringToInsert): string
    {
        return chunk_split($text, 1, $stringToInsert);
    }
}
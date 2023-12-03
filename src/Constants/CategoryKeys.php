<?php
namespace App\Constants;


class CategoryKeys
{
    public const BRAK = 'brak';
    public const DOFINANSOWANIE = 'dofinansowanie';
    public const KOSZTY = 'koszty';
    public const DAROWIZNA = 'darowizna';

    public const ALL_VALUES = [
        self::BRAK => 'Brak',
        self::DOFINANSOWANIE => 'Dofinansowanie',
        self::KOSZTY => 'Koszty',
        self::DAROWIZNA => 'Darowizna',
    ];
}
<?php

namespace App\Service;

class DateTimeHelper
{
    public static function format(\DateTimeInterface $dateTime, string $format = 'Y-m-d'): string
    {
        if (empty($dateTime)) {
            return 'BRAK_DATY';
        }
        return $dateTime->format($format);
    }

    /**
     * Utworzenie Daty ze string'a.
     * Przykładowo $string = '2022-07-17'.
     * @param string $string
     * @param string $format
     * @return \DateTimeInterface
     */
    public static function createDateFromString(string $string, string $format = 'Y-m-d'): \DateTimeInterface
    {
        return \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', "$string 00:00:00");
    }

    public static function firstDayOfMonth(\DateTimeInterface $date)
    {
        return \DateTime::createFromInterface($date)->modify('first day of this month')->setTime(0, 0);
    }
}
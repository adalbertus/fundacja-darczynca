<?php
namespace App\Twig;

use App\Constants\CategoryKeys;
use App\Constants\UserRolesKeys;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    protected $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    public function getFilters(): array
    {
        return [
            new TwigFilter('yesno', [$this, 'getYesNo']),
            new TwigFilter('categoryName', [$this, 'getCategoryName']),
            new TwigFilter('formatPLN', [$this, 'getFormatPLN']),
            new TwigFilter('date', [$this, 'getDate']),
            new TwigFilter('dateTime', [$this, 'getDateTime']),
            new TwigFilter('roleDescription', [$this, 'getRoleDescription']),
            // new TwigFilter('relativeTime', [$this, 'getRelativeTime']),
        ];
    }

    public function getYesNo($value)
    {
        if (is_bool($value) === true) {
            if ($value === true) {
                return 'Tak';
            }
            return 'Nie';
        }
        if (is_numeric($value)) {
            if ($value == 1) {
                return 'Tak';
            }
            return 'Nie';
        }
        return $value;
    }

    public function getCategoryName($category)
    {
        if (array_key_exists($category, CategoryKeys::ALL_VALUES)) {
            return CategoryKeys::ALL_VALUES[$category];
        } else {
            return $category;
        }
    }

    public function getRoleDescription($role)
    {
        if (array_key_exists($role, UserRolesKeys::ROLE_DESCRIPTIONS)) {
            return UserRolesKeys::ROLE_DESCRIPTIONS[$role];
        } else {
            return $role;
        }
    }


    public function getFormatPLN($value, $empty = '- zł')
    {
        if (is_numeric($value)) {
            return number_format(floatval($value), 2, '.', ' ') . ' zł';
        }
        if (is_null($value)) {
            return $empty;
        }
        return $value;
    }

    /**
     * @param \DateTimeInterface|\DateInterval|string $timestamp
     * @param string $fallback
     * @param string $format
     * @return string
     */
    public function getDate($timestamp, string $fallback = 'Brak', string $format = 'Y-m-d'): string
    {
        if ($timestamp !== null) {
            return twig_date_format_filter($this->twig, $timestamp, $format);
        }

        return $fallback;
    }

    /**
     * @param \DateTimeInterface|\DateInterval|string $timestamp
     * @param string $fallback
     * @param string $format
     * @return string
     */
    public function getDateTime($timestamp, string $fallback = 'Brak', string $format = 'Y-m-d H:m:s'): string
    {
        return self::getDate($timestamp, $fallback, $format);
    }

    function getRelativeTime($timestamp, string $fallback = 'Brak')
    {
        if ($timestamp === null) {
            return $fallback;
        }

        $diff = time() - $timestamp;
        if ($diff == 0)
            return 'teraz';
        elseif ($diff > 0) {
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 60)
                    return 'właśnie teraz';
                if ($diff < 120)
                    return '1 minutę temu';
                if ($diff < 3600)
                    return floor($diff / 60) . ' minut temu';
                if ($diff < 7200)
                    return '1 godzinę temu';
                if ($diff < 86400)
                    return floor($diff / 3600) . ' godzin temu';
            }
            if ($day_diff == 1)
                return 'wczoraj';
            if ($day_diff < 7)
                return $day_diff . ' dni temu';
            if ($day_diff < 31)
                return ceil($day_diff / 7) . ' tygodni temu';
            if ($day_diff < 60)
                return 'miesiąc temu';
            return date('F Y', $timestamp);
        } else {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if ($day_diff == 0) {
                if ($diff < 120)
                    return 'w ciągu minuty';
                if ($diff < 3600)
                    return 'w ' . floor($diff / 60) . ' minut';
                if ($diff < 7200)
                    return 'w ciągu godziny';
                if ($diff < 86400)
                    return 'w ' . floor($diff / 3600) . ' godzin';
            }
            if ($day_diff == 1)
                return 'Jutro';
            if ($day_diff < 4)
                return date('l', $timestamp);
            if ($day_diff < 7 + (7 - date('w')))
                return 'za tydzień';
            if (ceil($day_diff / 7) < 4)
                return 'w ' . ceil($day_diff / 7) . ' tygodni';
            if (date('n', $timestamp) == date('n') + 1)
                return 'za miesiąc';
            return date('F Y', $timestamp);
        }
    }
}
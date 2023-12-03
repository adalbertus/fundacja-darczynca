<?php
namespace App\Tests\Service;

use App\Service\StringHelper;
use PHPUnit\Framework\TestCase;


class StringHelperTest extends TestCase
{
    public function provideDataForDescriptionsAreNormalized(): array
    {
        return [
            ['ęĘóÓąĄśŚłŁżŻźŹćĆńŃ', 'eeooaassllzzzzccnn'],
            ['OND  II SZLACHTOWA  2022   JAcek Kłąb', 'ond ii szlachtowa 2022 jacek klab'],
            ['OND_2 SZLACHTOWA 2022_-_ALA_ma_kota   ', 'ond2 szlachtowa 2022alamakota'],
            ['OAZA III gdzieś w_fajnym_miejscu', 'oaza iii gdzies wfajnymmiejscu'],
            ['OR3 06 08 2021 KAZIMIERZ BISKUPI- I reneusz i OlgaJarosz z Zos ią', 'or3 06 08 2021 kazimierz biskupi i reneusz i olgajarosz z zos ia'],
        ];
    }

    /**
     * @dataProvider provideDataForDescriptionsAreNormalized
     */
    public function testDescriptionsAreNormalized(string $desc, string $expected)
    {
        $result = StringHelper::normalize($desc);
        $this->assertEquals($expected, $result);
    }

    public function testStringIsCorecctlyInserted()
    {
        $result = StringHelper::insertStringBetweenCharacters('ABC', '\s?');

        $this->assertEquals('A\s?B\s?C\s?', $result);
    }
}
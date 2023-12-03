<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BaseTestCase extends KernelTestCase
{
    public static function getFullPath($file): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . join(DIRECTORY_SEPARATOR, ['tests', 'test_data', $file]);
    }
}
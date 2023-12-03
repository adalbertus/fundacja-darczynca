<?php

namespace App\Tests\Entity;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use App\Factory\BankHistoryFactory;
use App\Factory\DonorFactory;
use App\Tests\DatabaseTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class BankHistoryTest extends DatabaseTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = static::getContainer()->get(ValidatorInterface::class);
    }

    public function testRawIsEmptyMd5IsGenerated(): void
    {
        $bh = new BankHistory();
        $bh->calculateMd5();

        $this->assertNotEmpty($bh->getMd5());
    }

    public function testRawIsSetMd5IsGenerated(): void
    {
        $bh = new BankHistory();
        $bh->setRaw('ala ma kota');
        $bh->calculateMd5();

        $this->assertEquals('49aa66843380c377e93b198b966eb699', $bh->getMd5());
    }

    public function testBankHistoryCategoryKosztyOK()
    {
        $bh = BankHistoryFactory::createOne([
            'category' => CategoryKeys::KOSZTY,
        ])->object();
        $errors = $this->validator->validate($bh);
        $this->assertEquals(0, count($errors));
    }

    public function testBankHistoryCategoryKosztyFieldsNotEmptyNOK()
    {
        $bh = BankHistoryFactory::createOne([
            'category' => CategoryKeys::KOSZTY,
            'donor' => DonorFactory::createOne()->object(),
        ])->object();
        $errors = $this->validator->validate($bh);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testBankHistoryCategoryDarowiznaOK()
    {
        $bh = BankHistoryFactory::createOne([
            'category' => CategoryKeys::DAROWIZNA,
            'donor' => DonorFactory::createOne()->object(),
        ])->object();
        $errors = $this->validator->validate($bh);
        $this->assertEquals(0, count($errors));
    }

    public function testBankHistoryCategoryDarowiznaNOK()
    {
        $bh = BankHistoryFactory::createOne([
            'category' => CategoryKeys::DAROWIZNA,
        ])->object();
        $errors = $this->validator->validate($bh);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testBankHistoryCategoryDofinansowanieOK()
    {
        $bh = BankHistoryFactory::createOne([
            'category' => CategoryKeys::DOFINANSOWANIE,
        ])->object();
        $errors = $this->validator->validate($bh);
        $this->assertEquals(0, count($errors));
    }

    public function testBankHistoryCategoryDofinansowanieNOK()
    {
        $bh = BankHistoryFactory::createOne([
            'category' => CategoryKeys::DOFINANSOWANIE,
            'donor' => DonorFactory::createOne()->object(),
        ])->object();
        $errors = $this->validator->validate($bh);
        $this->assertGreaterThan(0, count($errors));
    }
}
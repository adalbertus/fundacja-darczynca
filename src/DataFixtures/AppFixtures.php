<?php

namespace App\DataFixtures;

use App\Constants\CategoryKeys;
use App\Constants\UserRolesKeys;
use App\Factory\BankHistoryFactory;
use App\Factory\DescriptionRegexpFactory;
use App\Factory\DonorFactory;
use App\Factory\UserFactory;
use App\Tests\BaseTestCase;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class AppFixtures extends Fixture
{
    public const ADMIN_EMAIL = 'admin@example.com';
    public const USER_EMAIL = 'user@example.com';
    public const DONOR_EMAIL = 'donor@example.com';


    private $environment;

    public function __construct(ContainerBagInterface $params)
    {
        $this->environment = $params->get("environment");
    }

    public function load(ObjectManager $manager): void
    {
        if ($this->environment == 'dev') {
            $this->createDevEntities();
        }
        if ($this->environment == 'test') {
            $this->createUsers();
        }

        $manager->flush();
    }

    private function createDevEntities(): void
    {
        UserFactory::createMany(50);
        DonorFactory::createMany(50, ['is_auto' => false]);
        $donor = DonorFactory::createOne();
        BankHistoryFactory::createMany(50, [
            'is_draft' => false,
            'donor' => $donor,
            'category' => CategoryKeys::DAROWIZNA,
        ]);
    }

    private function createUsers(): void
    {
        // password: haslo
        UserFactory::createOne([
            'email' => self::ADMIN_EMAIL,
            'roles' => [UserRolesKeys::ADMIN, UserRolesKeys::USER],
            'password' => '$2y$13$lEPa0D6wP.QHxEzmzprB4Oz0S2pz7iXKCxXXGGoAVvPlvm36mMpVC'
        ]);

        UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'roles' => [UserRolesKeys::USER],
            'password' => '$2y$13$lEPa0D6wP.QHxEzmzprB4Oz0S2pz7iXKCxXXGGoAVvPlvm36mMpVC'
        ]);

        UserFactory::createOne([
            'email' => self::DONOR_EMAIL,
            'roles' => [UserRolesKeys::USER],
            'password' => '$2y$13$lEPa0D6wP.QHxEzmzprB4Oz0S2pz7iXKCxXXGGoAVvPlvm36mMpVC'
        ]);


        if ($this->environment == 'test') {
            $this->loadTestData();
        }
        // UserFactory::createMany(15);
    }

    private function loadFromSql(ObjectManager $manager, string $filename): void
    {
        $sqlFile = BaseTestCase::getFullPath($filename);
        $sql = file_get_contents($sqlFile);
        $stmt = $manager->getConnection()->prepare($sql);
        $stmt->executeQuery();
    }

    private function loadTestData(): void
    {
        DescriptionRegexpFactory::createOne([
            'expression' => 'na cele statutowe',
            'category' => CategoryKeys::DAROWIZNA,
        ]);
    }
}
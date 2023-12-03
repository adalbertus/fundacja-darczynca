<?php
namespace App\Tests;

use App\Tests\BaseTestCase;
use LogicException;
use Zenstruck\Foundry\Test\Factories;

class DatabaseTestCase extends BaseTestCase
{
    use Factories;
    // use ResetDatabase, Factories;

    protected $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        if ('test' !== self::$kernel->getEnvironment()) {
            throw new LogicException('Execution only in Test environment possible!');
        }
        $this->entityManager = self::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
        // $this->initDatabase();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    private function initDatabase(): void
    {
        // Nie mogę tworzyć zerowej bazy ze skonfigurowanymi testami wg
        // https://symfony.com/doc/current/testing.html#configuring-a-database-for-tests

        // $entityManager = self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        // $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        // $schemaTool = new SchemaTool($entityManager);
        // $schemaTool->updateSchema($metaData);
    }
}
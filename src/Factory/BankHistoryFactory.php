<?php

namespace App\Factory;

use App\Constants\CategoryKeys;
use App\Entity\BankHistory;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<BankHistory>
 *
 * @method        BankHistory|Proxy create(array|callable $attributes = [])
 * @method static BankHistory|Proxy createOne(array $attributes = [])
 * @method static BankHistory|Proxy find(object|array|mixed $criteria)
 * @method static BankHistory|Proxy findOrCreate(array $attributes)
 * @method static BankHistory|Proxy first(string $sortedField = 'id')
 * @method static BankHistory|Proxy last(string $sortedField = 'id')
 * @method static BankHistory|Proxy random(array $attributes = [])
 * @method static BankHistory|Proxy randomOrCreate(array $attributes = [])
 * @method static BankHistoryRepository|RepositoryProxy repository()
 * @method static BankHistory[]|Proxy[] all()
 * @method static BankHistory[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static BankHistory[]|Proxy[] createSequence(array|callable $sequence)
 * @method static BankHistory[]|Proxy[] findBy(array $attributes)
 * @method static BankHistory[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static BankHistory[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class BankHistoryFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     */
    protected function getDefaults(): array
    {
        $result = [
            'category' => CategoryKeys::BRAK,
            'sub_category' => CategoryKeys::BRAK,
            'date' => self::faker()->dateTimeBetween('-12 months'),
            'description' => self::faker()->sentence(5),
            'is_draft' => self::faker()->boolean(),
            'sender_bank_account' => substr(self::faker()->iban('PL', '', 26), 2),
            'sender_name' => self::faker()->name(),
            'value' => self::faker()->randomFloat(2, 10, 500),
            'manual' => false,
            'raw' => self::faker()->text(100),
        ];
        return $result;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function (BankHistory $bankHistory): void {
                $bankHistory->calculateMd5();
            })
        ;
    }

    protected static function getClass(): string
    {
        return BankHistory::class;
    }
}
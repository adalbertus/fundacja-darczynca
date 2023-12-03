<?php

namespace App\Factory;

use App\Entity\Donor;
use App\Repository\DonorRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Donor>
 *
 * @method        Donor|Proxy                     create(array|callable $attributes = [])
 * @method static Donor|Proxy                     createOne(array $attributes = [])
 * @method static Donor|Proxy                     find(object|array|mixed $criteria)
 * @method static Donor|Proxy                     findOrCreate(array $attributes)
 * @method static Donor|Proxy                     first(string $sortedField = 'id')
 * @method static Donor|Proxy                     last(string $sortedField = 'id')
 * @method static Donor|Proxy                     random(array $attributes = [])
 * @method static Donor|Proxy                     randomOrCreate(array $attributes = [])
 * @method static DonorRepository|RepositoryProxy repository()
 * @method static Donor[]|Proxy[]                 all()
 * @method static Donor[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static Donor[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static Donor[]|Proxy[]                 findBy(array $attributes)
 * @method static Donor[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static Donor[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class DonorFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'name' => self::faker()->unique()->firstName(),
            'updatedAt' => self::faker()->dateTime(),
            'is_auto' => false,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Donor $donor): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Donor::class;
    }
}

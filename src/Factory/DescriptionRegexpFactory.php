<?php

namespace App\Factory;

use App\Entity\DescriptionRegexp;
use App\Repository\DescriptionRegexpRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<DescriptionRegexp>
 *
 * @method        DescriptionRegexp|Proxy create(array|callable $attributes = [])
 * @method static DescriptionRegexp|Proxy createOne(array $attributes = [])
 * @method static DescriptionRegexp|Proxy find(object|array|mixed $criteria)
 * @method static DescriptionRegexp|Proxy findOrCreate(array $attributes)
 * @method static DescriptionRegexp|Proxy first(string $sortedField = 'id')
 * @method static DescriptionRegexp|Proxy last(string $sortedField = 'id')
 * @method static DescriptionRegexp|Proxy random(array $attributes = [])
 * @method static DescriptionRegexp|Proxy randomOrCreate(array $attributes = [])
 * @method static DescriptionRegexpRepository|RepositoryProxy repository()
 * @method static DescriptionRegexp[]|Proxy[] all()
 * @method static DescriptionRegexp[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static DescriptionRegexp[]|Proxy[] createSequence(array|callable $sequence)
 * @method static DescriptionRegexp[]|Proxy[] findBy(array $attributes)
 * @method static DescriptionRegexp[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static DescriptionRegexp[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class DescriptionRegexpFactory extends ModelFactory
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
            'category' => self::faker()->text(32),
            'expression' => self::faker()->text(500),
            'sub_category' => self::faker()->text(32),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(DescriptionRegexp $descriptionRegexp): void {})
        ;
    }

    protected static function getClass(): string
    {
        return DescriptionRegexp::class;
    }
}

<?php

namespace App\Factory;

use App\Entity\Vehicle;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;

/**
 * @extends ModelFactory<Vehicle>
 *
 * @method static Vehicle|Proxy createOne(array $attributes = [])
 * @method static Vehicle[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Vehicle|Proxy find(object|array|mixed $criteria)
 * @method static Vehicle|Proxy findOrCreate(array $attributes)
 * @method static Vehicle|Proxy first(string $sortedField = 'id')
 * @method static Vehicle|Proxy last(string $sortedField = 'id')
 * @method static Vehicle|Proxy random(array $attributes = [])
 * @method static Vehicle|Proxy randomOrCreate(array $attributes = [])
 * @method static Vehicle[]|Proxy[] all()
 * @method static Vehicle[]|Proxy[] findBy(array $attributes)
 * @method static Vehicle[]|Proxy[] randomSet(int $number, array $attributes = [])
 * @method static Vehicle[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method Vehicle|Proxy create(array|callable $attributes = [])
 */
final class VehicleFactory extends ModelFactory
{
    public function __construct()
    {
        parent::__construct();

        // TODO inject services if required (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services)
    }

    protected function getDefaults(): array
    {
        return [
            // TODO add your default values here (https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories)
            'dateAdded' => self::faker()->dateTime('now'),
            'type' => ['new', 'used'][rand(0, 1)],
            'msrp' => self::faker()->randomFloat(2,10000,80000),
            'year' => rand(1900, ((int)date("Y")) + 1),
            'make' => self::faker()->text(10),
            'model' => self::faker()->text(10),
            'miles' => rand(1000, 50000),
            'vin' => self::faker()->unique()->creditCardNumber(),
            'deleted' =>rand(0, 1)
        ];
    }

    protected function initialize(): self
    {
        // see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
        return $this// ->afterInstantiate(function(Vehicle $vehicle): void {})
            ;
    }

    protected static function getClass(): string
    {
        return Vehicle::class;
    }
}

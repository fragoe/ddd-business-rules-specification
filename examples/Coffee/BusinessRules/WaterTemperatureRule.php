<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;

/**
 * Business Rule: Water temperature must be between 0 and 100°C.
 *
 * Physical constraints:
 * - Minimum 0°C: Freezing point (cold brew can use refrigerated water)
 * - Maximum 100°C: Boiling point (though typically use 90-96°C)
 *
 * Note: Cold brew uses 4-22°C, while hot methods use 80-96°C
 */
class WaterTemperatureRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.water.temperature.invalid';
    protected const MESSAGE = 'Water temperature must be between 0 and 100°C';

    private const MIN_CELSIUS = 0;
    private const MAX_CELSIUS = 100;

    public function isSatisfiedBy($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $celsius = (float)$value;
        return $celsius >= self::MIN_CELSIUS && $celsius <= self::MAX_CELSIUS;
    }
}

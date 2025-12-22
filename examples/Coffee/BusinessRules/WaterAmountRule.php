<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;

/**
 * Business Rule: Water amount must be between 25 and 1000 milliliters.
 *
 * Typical brewing ranges:
 * - Minimum 25ml: Single espresso shot
 * - Maximum 1000ml: Large French press or batch cold brew
 */
class WaterAmountRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.water.amount.invalid';
    protected const MESSAGE = 'Water amount must be between 25 and 1000 milliliters';

    private const MIN_ML = 25;
    private const MAX_ML = 1000;

    public function isSatisfiedBy($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $ml = (float)$value;
        return $ml >= self::MIN_ML && $ml <= self::MAX_ML;
    }
}

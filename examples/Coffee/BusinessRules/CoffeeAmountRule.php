<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;

/**
 * Business Rule: Coffee amount must be between 5 and 60 grams.
 *
 * This covers typical single-serve to small batch brewing:
 * - Minimum 5g: Enough for extraction (anything less is too weak)
 * - Maximum 60g: Reasonable for home brewing (larger needs commercial equipment)
 */
class CoffeeAmountRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.amount.invalid';
    protected const MESSAGE = 'Coffee amount must be between 5 and 60 grams';

    private const MIN_GRAMS = 5;
    private const MAX_GRAMS = 60;

    public function isSatisfiedBy($value): bool
    {
        if (!is_numeric($value)) {
            return false;
        }

        $grams = (float)$value;
        return $grams >= self::MIN_GRAMS && $grams <= self::MAX_GRAMS;
    }
}

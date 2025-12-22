<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;

/**
 * Business Rule: Coffee-to-water ratio must be between 1:10 and 1:20.
 *
 * The brew ratio determines coffee strength:
 * - 1:10 (strong): 20g coffee → 200ml water (concentrated, cold brew)
 * - 1:15 (balanced): 20g coffee → 300ml water (most methods)
 * - 1:20 (mild): 20g coffee → 400ml water (weaker brew)
 *
 * Ratios outside this range are either too strong (sludge) or too weak (flavored water).
 *
 * Expects an array with 'coffee' (grams) and 'water' (ml) keys.
 */
class BrewRatioRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.brew.ratio.invalid';
    protected const MESSAGE = 'Coffee-to-water ratio must be between 1:10 and 1:20';

    private const MIN_RATIO = 10;  // 1:10 (strong)
    private const MAX_RATIO = 20;  // 1:20 (mild)

    public function isSatisfiedBy($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        if (!isset($value['coffee']) || !isset($value['water'])) {
            return false;
        }

        $coffee = (float)$value['coffee'];
        $water = (float)$value['water'];

        if ($coffee <= 0) {
            return false;
        }

        // Calculate ratio: water / coffee (e.g., 300ml / 20g = 15, which is 1:15)
        $ratio = $water / $coffee;

        return $ratio >= self::MIN_RATIO && $ratio <= self::MAX_RATIO;
    }
}

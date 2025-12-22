<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;

/**
 * Business Rule: Brew time must be between 10 seconds and 24 hours.
 *
 * Brew time ranges:
 * - Minimum 10 seconds: Very quick (shortest espresso shots)
 * - Maximum 24 hours (86,400 seconds): Longest cold brew
 *
 * Typical ranges:
 * - Espresso: 20-30 seconds
 * - Pour Over: 2.5-4 minutes
 * - French Press: 4-5 minutes
 * - Cold Brew: 12-24 hours
 */
class BrewTimeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.brew.time.invalid';
    protected const MESSAGE = 'Brew time must be between 10 seconds and 24 hours';

    private const MIN_SECONDS = 10;
    private const MAX_SECONDS = 86400; // 24 hours

    public function isSatisfiedBy($value): bool
    {
        if (!is_int($value) && !is_float($value)) {
            return false;
        }

        $seconds = (int)$value;
        return $seconds >= self::MIN_SECONDS && $seconds <= self::MAX_SECONDS;
    }
}

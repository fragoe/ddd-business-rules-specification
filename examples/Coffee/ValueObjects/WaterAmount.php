<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\WaterAmountRule;

/**
 * Water Amount - Volume of water in milliliters.
 *
 * Water amount determines:
 * - Final brew volume
 * - Coffee-to-water ratio (strength)
 * - Extraction rate
 *
 * Typical ranges:
 * - Single espresso: 25-35ml
 * - Double espresso: 36-50ml
 * - Single cup: 200-350ml
 * - French press (4 cups): 500-800ml
 */
final readonly class WaterAmount
{
    private function __construct(private float $milliliters)
    {
        $rule = new WaterAmountRule();
        if (!$rule->isSatisfiedBy($this->milliliters)) {
            throw new \InvalidArgumentException($rule->getMessage());
        }
    }

    public static function fromMilliliters(int|float $milliliters): self
    {
        return new self((float)$milliliters);
    }

    public function toMilliliters(): float
    {
        return $this->milliliters;
    }

    public function equals(self $other): bool
    {
        return abs($this->milliliters - $other->milliliters) < 1.0;
    }

    public function __toString(): string
    {
        return number_format($this->milliliters, 0) . 'ml';
    }
}

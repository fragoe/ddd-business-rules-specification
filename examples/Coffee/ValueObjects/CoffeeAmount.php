<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\CoffeeAmountRule;

/**
 * Coffee Amount - Mass of coffee grounds in grams.
 *
 * Typical single-serve brewing uses 5-60 grams:
 * - Single espresso: 7-9g
 * - Double espresso: 14-18g
 * - Single cup pour over: 15-20g
 * - French press (4 cups): 30-40g
 * - Cold brew concentrate: 50-60g
 */
final readonly class CoffeeAmount
{
    private function __construct(private float $grams)
    {
        $rule = new CoffeeAmountRule();
        if (!$rule->isSatisfiedBy($this->grams)) {
            throw new \InvalidArgumentException($rule->getMessage());
        }
    }

    public static function fromGrams(int|float $grams): self
    {
        return new self((float)$grams);
    }

    public function toGrams(): float
    {
        return $this->grams;
    }

    public function equals(self $other): bool
    {
        return abs($this->grams - $other->grams) < 0.1;
    }

    public function __toString(): string
    {
        return number_format($this->grams, 1) . 'g';
    }
}

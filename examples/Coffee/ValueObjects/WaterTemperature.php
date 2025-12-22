<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\WaterTemperatureRule;

/**
 * Water Temperature - Brewing water temperature in Celsius.
 *
 * Temperature affects extraction chemistry:
 * - Higher temp (90-96°C): Faster extraction, more bitterness, full body
 * - Lower temp (80-89°C): Slower extraction, more sweetness, lighter body
 * - Cold (4-22°C): Very slow extraction (cold brew), smooth and sweet
 *
 * Optimal ranges by method:
 * - Espresso: 90-96°C (need fast extraction)
 * - Pour Over: 90-96°C (control with pouring)
 * - French Press: 92-96°C (immersion, slightly lower OK)
 * - AeroPress: 80-95°C (very forgiving)
 * - Cold Brew: 4-22°C (room temp or refrigerated)
 */
final readonly class WaterTemperature
{
    private function __construct(private float $celsius)
    {
        $rule = new WaterTemperatureRule();
        if (!$rule->isSatisfiedBy($this->celsius)) {
            throw new \InvalidArgumentException($rule->getMessage());
        }
    }

    public static function fromCelsius(int|float $celsius): self
    {
        return new self((float)$celsius);
    }

    public function toCelsius(): float
    {
        return $this->celsius;
    }

    public function toFahrenheit(): float
    {
        return ($this->celsius * 9/5) + 32;
    }

    public function isCold(): bool
    {
        return $this->celsius < 30;
    }

    public function isHot(): bool
    {
        return $this->celsius >= 80;
    }

    public function equals(self $other): bool
    {
        return abs($this->celsius - $other->celsius) < 0.5;
    }

    public function __toString(): string
    {
        return number_format($this->celsius, 1) . '°C';
    }
}

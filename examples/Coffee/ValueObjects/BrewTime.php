<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\BrewTimeRule;

/**
 * Brew Time - Duration of coffee extraction in seconds.
 *
 * Brew time controls extraction:
 * - Under-extracted (too fast): Sour, weak, thin
 * - Properly extracted: Balanced, sweet, complex
 * - Over-extracted (too slow): Bitter, harsh, astringent
 *
 * Typical brew times:
 * - Espresso: 20-30 seconds (pressure brewing)
 * - Pour Over: 2.5-4 minutes (150-240 seconds)
 * - French Press: 4-5 minutes (240-300 seconds)
 * - AeroPress: 1-2 minutes (60-120 seconds)
 * - Moka Pot: 4-5 minutes (240-300 seconds)
 * - Cold Brew: 12-24 hours (43,200-86,400 seconds!)
 */
final readonly class BrewTime
{
    private function __construct(private int $seconds)
    {
        $rule = new BrewTimeRule();
        if (!$rule->isSatisfiedBy($this->seconds)) {
            throw new \InvalidArgumentException($rule->getMessage());
        }
    }

    public static function fromSeconds(int $seconds): self
    {
        return new self($seconds);
    }

    public static function fromMinutes(int|float $minutes): self
    {
        return new self((int)round($minutes * 60));
    }

    public static function fromHours(int|float $hours): self
    {
        return new self((int)round($hours * 3600));
    }

    public function toSeconds(): int
    {
        return $this->seconds;
    }

    public function toMinutes(): float
    {
        return $this->seconds / 60;
    }

    public function toHours(): float
    {
        return $this->seconds / 3600;
    }

    public function equals(self $other): bool
    {
        return $this->seconds === $other->seconds;
    }

    public function __toString(): string
    {
        if ($this->seconds < 60) {
            return $this->seconds . 's';
        } elseif ($this->seconds < 3600) {
            $minutes = round($this->seconds / 60, 1);
            return $minutes . 'min';
        } else {
            $hours = round($this->seconds / 3600, 1);
            return $hours . 'h';
        }
    }
}

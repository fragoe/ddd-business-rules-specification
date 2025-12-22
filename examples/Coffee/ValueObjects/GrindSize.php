<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

/**
 * Grind Size - Particle size of ground coffee.
 *
 * Grind size dramatically affects extraction:
 * - Finer = More surface area = Faster extraction = Stronger, more bitter
 * - Coarser = Less surface area = Slower extraction = Weaker, more sour
 *
 * Each brew method requires specific grind size for optimal extraction.
 */
enum GrindSize: string
{
    case EXTRA_FINE = 'extra_fine';      // Like powder (Turkish coffee)
    case FINE = 'fine';                  // Like table salt (Espresso)
    case MEDIUM_FINE = 'medium_fine';    // Like sand (AeroPress, Pour Over)
    case MEDIUM = 'medium';              // Like sea salt (Drip coffee)
    case MEDIUM_COARSE = 'medium_coarse'; // Like coarse sand (Chemex)
    case COARSE = 'coarse';              // Like breadcrumbs (French Press)
    case EXTRA_COARSE = 'extra_coarse';  // Like peppercorns (Cold Brew)

    /**
     * Get human-readable name.
     */
    public function getName(): string
    {
        return match($this) {
            self::EXTRA_FINE => 'Extra Fine',
            self::FINE => 'Fine',
            self::MEDIUM_FINE => 'Medium-Fine',
            self::MEDIUM => 'Medium',
            self::MEDIUM_COARSE => 'Medium-Coarse',
            self::COARSE => 'Coarse',
            self::EXTRA_COARSE => 'Extra Coarse',
        };
    }

    /**
     * Get visual comparison.
     */
    public function getComparison(): string
    {
        return match($this) {
            self::EXTRA_FINE => 'Like flour or powder',
            self::FINE => 'Like table salt',
            self::MEDIUM_FINE => 'Like fine sand',
            self::MEDIUM => 'Like sea salt',
            self::MEDIUM_COARSE => 'Like coarse sand',
            self::COARSE => 'Like breadcrumbs',
            self::EXTRA_COARSE => 'Like cracked peppercorns',
        };
    }

    /**
     * Get numeric value for comparison (1-7).
     */
    public function getValue(): int
    {
        return match($this) {
            self::EXTRA_FINE => 1,
            self::FINE => 2,
            self::MEDIUM_FINE => 3,
            self::MEDIUM => 4,
            self::MEDIUM_COARSE => 5,
            self::COARSE => 6,
            self::EXTRA_COARSE => 7,
        };
    }

    /**
     * Check if this grind is finer than another.
     */
    public function isFinerThan(self $other): bool
    {
        return $this->getValue() < $other->getValue();
    }

    /**
     * Check if this grind is coarser than another.
     */
    public function isCoarserThan(self $other): bool
    {
        return $this->getValue() > $other->getValue();
    }
}

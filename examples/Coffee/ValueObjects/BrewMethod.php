<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

/**
 * Brew Method - The way coffee is extracted from grounds.
 *
 * Each method has unique characteristics:
 * - ESPRESSO: High pressure, fine grind, quick extraction
 * - POUR_OVER: Manual pouring, medium grind, clean taste
 * - FRENCH_PRESS: Immersion, coarse grind, full body
 * - COLD_BREW: Long cold extraction, smooth and sweet
 * - AEROPRESS: Pressure + immersion, versatile
 * - MOKA_POT: Stovetop pressure, strong coffee
 */
enum BrewMethod: string
{
    case ESPRESSO = 'espresso';
    case POUR_OVER = 'pour_over';
    case FRENCH_PRESS = 'french_press';
    case COLD_BREW = 'cold_brew';
    case AEROPRESS = 'aeropress';
    case MOKA_POT = 'moka_pot';

    /**
     * Get human-readable name.
     */
    public function getName(): string
    {
        return match($this) {
            self::ESPRESSO => 'Espresso',
            self::POUR_OVER => 'Pour Over',
            self::FRENCH_PRESS => 'French Press',
            self::COLD_BREW => 'Cold Brew',
            self::AEROPRESS => 'AeroPress',
            self::MOKA_POT => 'Moka Pot',
        };
    }

    /**
     * Get description of this brew method.
     */
    public function getDescription(): string
    {
        return match($this) {
            self::ESPRESSO => 'Pressurized hot water forced through fine coffee grounds, creating concentrated coffee with crema',
            self::POUR_OVER => 'Hot water poured over coffee in a filter, highlighting clarity and nuanced flavors',
            self::FRENCH_PRESS => 'Coffee steeped in hot water then pressed through a metal filter, full-bodied with oils',
            self::COLD_BREW => 'Coffee grounds steeped in cold water for 12-24 hours, smooth and naturally sweet',
            self::AEROPRESS => 'Immersion brewing with air pressure, versatile and quick with clean taste',
            self::MOKA_POT => 'Stovetop brewer using steam pressure, strong and bold like espresso',
        };
    }
}

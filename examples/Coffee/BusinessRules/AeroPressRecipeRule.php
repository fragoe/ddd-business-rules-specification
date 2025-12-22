<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;

/**
 * Business Rule: AeroPress recipe must meet specific requirements.
 *
 * AeroPress is the most versatile brewing method! Invented in 2005, it combines
 * immersion brewing with gentle air pressure. It's forgiving, portable, and
 * can make anything from espresso-style to pour-over-style coffee.
 *
 * Requirements:
 * - Grind Size: FINE to MEDIUM - Very forgiving range!
 * - Water Temp: 80-95°C - Wide range works (even cold brew style!)
 * - Brew Time: 1-2 minutes - Quick brewing
 * - Ratio: 1:12 to 1:16 - Adjustable for strength preference
 *
 * Why these matter:
 * - Flexible grind: Pressure compensates - fine for strong, medium for mild
 * - Temp range: Lower temps work due to pressure (less risk of bitterness)
 * - 1-2 minutes: Immersion + pressure = quick extraction
 * - Ratio flexibility: Can make concentrated (dilute) or regular strength
 *
 * Two main methods:
 * 1. Standard: Coffee in, water in, steep, press (like tea)
 * 2. Inverted: Upside down, more control, flip and press (competition style)
 *
 * Fun facts:
 * - World AeroPress Championship exists (WAC)!
 * - Over 10,000 recipes documented online
 * - Favorite of travelers and campers (nearly indestructible)
 * - Can make cold brew in 2 minutes (with cold water + long steep)
 *
 * Accepts BrewRecipeData with all brewing parameters.
 */
class AeroPressRecipeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.aeropress.recipe.invalid';
    protected const MESSAGE = 'AeroPress requires: fine to medium grind, 80-95°C water, 1-2 minute brew, 1:12-1:16 ratio';

    /**
     * @param BrewRecipeData $value
     */
    public function isSatisfiedBy($value): bool
    {
        if (!($value instanceof BrewRecipeData)) {
            return false;
        }

        // 1. Grind can be FINE, MEDIUM_FINE, or MEDIUM (very forgiving!)
        $allowedGrinds = [GrindSize::FINE, GrindSize::MEDIUM_FINE, GrindSize::MEDIUM];
        if (!in_array($value->grindSize, $allowedGrinds, true)) {
            return false;
        }

        // 2. Water temperature: 80-95°C (very forgiving range)
        if ($value->waterTemp < 80 || $value->waterTemp > 95) {
            return false;
        }

        // 3. Brew time: 1-2 minutes (60-120 seconds)
        if ($value->brewTime < 60 || $value->brewTime > 120) {
            return false;
        }

        // 4. Ratio: 1:12 to 1:16 (adjustable for strength)
        $ratio = $value->getRatio();
        if ($ratio < 12 || $ratio > 16) {
            return false;
        }

        return true;
    }
}

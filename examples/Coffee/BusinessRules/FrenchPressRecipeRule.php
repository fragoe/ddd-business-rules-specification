<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;

/**
 * Business Rule: French Press recipe must meet specific requirements.
 *
 * French Press (also called cafetière or coffee plunger) is an immersion method
 * where coffee grounds steep in hot water, then are separated by pressing a metal
 * mesh filter down. This allows coffee oils through (unlike paper filters), creating
 * a full-bodied, rich cup.
 *
 * Requirements:
 * - Grind Size: COARSE (like breadcrumbs) - Prevents over-extraction and sludge
 * - Water Temp: 92-96°C - Hot water for good extraction during steeping
 * - Brew Time: 4-5 minutes - Optimal steeping time for balanced extraction
 * - Ratio: 1:15 to 1:17 - Standard strength (e.g., 30g → 450-510ml)
 *
 * Why these matter:
 * - Coarse grind: Prevents over-extraction during long contact time and keeps grounds
 *   from passing through the metal filter (finer = muddy coffee)
 * - Hot water: Needed for full extraction during the 4-minute steep
 * - 4-5 minutes: Sweet spot - less = under-extracted, more = over-extracted
 * - 1:16 ratio: Balanced strength that highlights the full body
 *
 * Accepts BrewRecipeData with all brewing parameters.
 */
class FrenchPressRecipeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.french-press.recipe.invalid';
    protected const MESSAGE = 'French Press requires: coarse grind, 92-96°C water, 4-5 minute steep, 1:15-1:17 ratio';

    /**
     * @param BrewRecipeData $value
     */
    public function isSatisfiedBy($value): bool
    {
        if (!($value instanceof BrewRecipeData)) {
            return false;
        }

        // 1. Grind must be COARSE (prevents over-extraction and sludge)
        if ($value->grindSize !== GrindSize::COARSE) {
            return false;
        }

        // 2. Water temperature: 92-96°C (hot for proper extraction)
        if ($value->waterTemp < 92 || $value->waterTemp > 96) {
            return false;
        }

        // 3. Brew time: 4-5 minutes (240-300 seconds)
        if ($value->brewTime < 240 || $value->brewTime > 300) {
            return false;
        }

        // 4. Ratio: 1:15 to 1:17 (balanced strength)
        $ratio = $value->getRatio();
        if ($ratio < 15 || $ratio > 17) {
            return false;
        }

        return true;
    }
}

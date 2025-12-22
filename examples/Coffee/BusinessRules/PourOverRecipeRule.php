<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;

/**
 * Business Rule: Pour Over recipe must meet specific requirements.
 *
 * Pour Over (V60, Chemex, Kalita Wave) is a manual brewing method where you pour
 * hot water over coffee grounds in a filter. The slow, controlled pour allows
 * precise extraction, highlighting delicate flavors and creating a clean, bright cup.
 *
 * Requirements:
 * - Grind Size: MEDIUM_FINE to MEDIUM - Allows proper flow rate
 * - Water Temp: 90-96°C - Hot water for good extraction
 * - Brew Time: 2.5-4 minutes - Total pour time including bloom
 * - Ratio: 1:15 to 1:17 - Balanced, highlighting flavor clarity
 *
 * Why these matter:
 * - Medium-fine grind: Balances extraction and flow rate (too fine = slow,
 *   too coarse = under-extracted)
 * - Hot water: Needed for proper extraction in the relatively short contact time
 * - 2.5-4 minutes: Includes 30-45 second "bloom" (first pour) + remaining pours
 * - 1:16 ratio: Sweet spot for highlighting subtle flavors
 *
 * Brewing technique:
 * 1. Bloom: Pour 2x coffee weight in water, wait 30-45 seconds (CO2 escape)
 * 2. Main pours: Slow, circular pours to maintain water level
 * 3. Total time: Should finish between 2.5-4 minutes
 *
 * Different devices:
 * - V60: Conical with large hole, faster flow (use finer grind)
 * - Chemex: Thick filter, slower flow (use coarser grind)
 * - Kalita Wave: Flat bottom, even extraction (very forgiving)
 *
 * Accepts BrewRecipeData with all brewing parameters.
 */
class PourOverRecipeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.pour-over.recipe.invalid';
    protected const MESSAGE = 'Pour Over requires: medium-fine to medium grind, 90-96°C water, 2.5-4 minute brew, 1:15-1:17 ratio';

    /**
     * @param BrewRecipeData $value
     */
    public function isSatisfiedBy($value): bool
    {
        if (!($value instanceof BrewRecipeData)) {
            return false;
        }

        // 1. Grind must be MEDIUM_FINE or MEDIUM
        if ($value->grindSize !== GrindSize::MEDIUM_FINE && $value->grindSize !== GrindSize::MEDIUM) {
            return false;
        }

        // 2. Water temperature: 90-96°C (hot for good extraction)
        if ($value->waterTemp < 90 || $value->waterTemp > 96) {
            return false;
        }

        // 3. Brew time: 2.5-4 minutes (150-240 seconds)
        if ($value->brewTime < 150 || $value->brewTime > 240) {
            return false;
        }

        // 4. Ratio: 1:15 to 1:17 (balanced for flavor clarity)
        $ratio = $value->getRatio();
        if ($ratio < 15 || $ratio > 17) {
            return false;
        }

        return true;
    }
}

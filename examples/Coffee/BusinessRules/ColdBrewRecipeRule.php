<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;

/**
 * Business Rule: Cold Brew recipe must meet specific requirements.
 *
 * Cold Brew uses time instead of heat to extract coffee. Cold water extracts
 * different compounds than hot water, resulting in a smooth, naturally sweet,
 * less acidic coffee that's perfect over ice.
 *
 * Requirements:
 * - Grind Size: EXTRA_COARSE or COARSE - Very long contact time requires coarse grind
 * - Water Temp: 4-22°C - Room temperature or refrigerated (NOT hot!)
 * - Brew Time: 12-24 hours - Patience is key! Most common: 16-18 hours
 * - Ratio: 1:5 to 1:8 - Concentrated (dilute with water/milk when serving)
 *
 * Why these matter:
 * - Extra coarse grind: Long contact time would over-extract finer grinds
 * - Cold water: Extracts different compounds (less acidity, bitterness)
 * - 12-24 hours: Cold water extracts MUCH slower than hot water
 * - 1:5-1:8 ratio: Creates a concentrate that you dilute 1:1 when serving
 *
 * Fun facts:
 * - Cold brew has 2-3x less acidity than hot brew
 * - It's naturally sweeter (fewer bitter compounds extracted)
 * - Stays fresh in fridge for up to 2 weeks!
 * - Originally invented in Japan (Kyoto-style drip)
 *
 * Accepts BrewRecipeData with all brewing parameters.
 */
class ColdBrewRecipeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.cold-brew.recipe.invalid';
    protected const MESSAGE = 'Cold Brew requires: extra coarse grind, 4-22°C water, 12-24 hour steep, 1:5-1:8 ratio';

    /**
     * @param BrewRecipeData $value
     */
    public function isSatisfiedBy($value): bool
    {
        if (!($value instanceof BrewRecipeData)) {
            return false;
        }

        // 1. Grind must be EXTRA_COARSE or COARSE
        if ($value->grindSize !== GrindSize::EXTRA_COARSE && $value->grindSize !== GrindSize::COARSE) {
            return false;
        }

        // 2. Water temperature: 4-22°C (cold or room temp, NOT hot!)
        if ($value->waterTemp < 4 || $value->waterTemp > 22) {
            return false;
        }

        // 3. Brew time: 12-24 hours (43,200-86,400 seconds)
        if ($value->brewTime < 43200 || $value->brewTime > 86400) {
            return false;
        }

        // 4. Ratio: 1:5 to 1:8 (concentrated - dilute when serving)
        $ratio = $value->getRatio();
        if ($ratio < 5 || $ratio > 8) {
            return false;
        }

        return true;
    }
}

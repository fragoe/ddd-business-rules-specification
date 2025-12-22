<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;

/**
 * Business Rule: Moka Pot recipe must meet specific requirements.
 *
 * The Moka Pot (invented in 1933 by Alfonso Bialetti) is a stovetop brewer
 * that uses steam pressure (1-2 bars) to push water through coffee grounds.
 * It produces a strong, concentrated coffee often called "stovetop espresso"
 * (though it's technically not espresso due to lower pressure).
 *
 * Requirements:
 * - Grind Size: FINE to MEDIUM_FINE - Finer than drip, coarser than espresso
 * - Water Temp: 70-95°C - Start with hot water to avoid burning coffee
 * - Brew Time: 3-5 minutes - From heat on to coffee finished
 * - Ratio: 1:7 to 1:10 - Concentrated but less than true espresso
 *
 * Why these matter:
 * - Fine-medium grind: Too fine = bitter, clogged; too coarse = weak, watery
 * - Hot starting water: Prevents the grounds from "cooking" while pot heats up
 * - 3-5 minutes: Faster = under-extracted, slower = over-extracted/burnt taste
 * - 1:7-1:10 ratio: Creates the signature strong, bold Moka Pot flavor
 *
 * Pro tips:
 * - Use pre-heated water to reduce time on heat (less bitter)
 * - Remove from heat as soon as coffee starts sputtering
 * - Don't tamp the grounds (unlike espresso)
 * - Run cold water over base when done to stop extraction
 *
 * Fun facts:
 * - The iconic 8-sided design is still used today (Bialetti Moka Express)
 * - Over 300 million Moka Pots have been sold worldwide
 * - In Italy, nearly every household has one (90%!)
 * - The original design is displayed in the MoMA in New York
 *
 * Accepts BrewRecipeData with all brewing parameters.
 */
class MokaPotRecipeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.moka-pot.recipe.invalid';
    protected const MESSAGE = 'Moka Pot requires: fine to medium-fine grind, 70-95°C water, 3-5 minute brew, 1:7-1:10 ratio';

    /**
     * @param BrewRecipeData $value
     */
    public function isSatisfiedBy($value): bool
    {
        if (!($value instanceof BrewRecipeData)) {
            return false;
        }

        // 1. Grind must be FINE or MEDIUM_FINE
        if ($value->grindSize !== GrindSize::FINE && $value->grindSize !== GrindSize::MEDIUM_FINE) {
            return false;
        }

        // 2. Water temperature: 70-95°C (start with hot water, not boiling)
        if ($value->waterTemp < 70 || $value->waterTemp > 95) {
            return false;
        }

        // 3. Brew time: 3-5 minutes (180-300 seconds)
        if ($value->brewTime < 180 || $value->brewTime > 300) {
            return false;
        }

        // 4. Ratio: 1:7 to 1:10 (concentrated, but less than espresso)
        $ratio = $value->getRatio();
        if ($ratio < 7 || $ratio > 10) {
            return false;
        }

        return true;
    }
}

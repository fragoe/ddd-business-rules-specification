<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules;

use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;

/**
 * Business Rule: Espresso recipe must meet specific requirements.
 *
 * Espresso is unique - it uses high pressure (9 bars) to force hot water through
 * finely ground coffee in a very short time. This creates a concentrated shot
 * with a layer of crema (golden foam) on top.
 *
 * Requirements:
 * - Grind Size: FINE (like table salt) - Needed to create resistance for pressure
 * - Water Temp: 90-96°C - Hot enough for quick extraction, not boiling
 * - Brew Time: 20-35 seconds - The "golden time" for balanced extraction
 * - Ratio: 1:1.8 to 1:2.5 - Very concentrated (e.g., 18g → 36-45ml)
 *
 * Why these matter:
 * - Fine grind: Creates necessary resistance for 9-bar pressure
 * - Hot water: Extracts quickly in the short contact time
 * - 20-35 seconds: Under = sour, Over = bitter
 * - 1:2 ratio: Concentrated enough to showcase coffee's intensity
 *
 * Accepts BrewRecipeData with all brewing parameters.
 */
class EspressoRecipeRule extends CompositeBusinessRule
{
    protected const CODE = 'coffee.espresso.recipe.invalid';
    protected const MESSAGE = 'Espresso requires: fine grind, 90-96°C water, 20-35 second extraction, 1:1.8-1:2.5 ratio';

    /**
     * @param BrewRecipeData $value
     */
    public function isSatisfiedBy($value): bool
    {
        if (!($value instanceof BrewRecipeData)) {
            return false;
        }

        // 1. Grind must be FINE (espresso-specific)
        if ($value->grindSize !== GrindSize::FINE) {
            return false;
        }

        // 2. Water temperature: 90-96°C (hot but not boiling)
        if ($value->waterTemp < 90 || $value->waterTemp > 96) {
            return false;
        }

        // 3. Brew time: 20-35 seconds (the golden extraction window)
        if ($value->brewTime < 20 || $value->brewTime > 35) {
            return false;
        }

        // 4. Ratio: 1:1.8 to 1:2.5 (very concentrated)
        $ratio = $value->getRatio();
        if ($ratio < 1.8 || $ratio > 2.5) {
            return false;
        }

        return true;
    }
}

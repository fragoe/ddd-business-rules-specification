<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee;

use fragoe\DDDBusinessRules\BusinessRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\AeroPressRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\ColdBrewRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\EspressoRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\FrenchPressRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\MokaPotRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\PourOverRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewMethod;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewTime;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\CoffeeAmount;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\WaterAmount;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\WaterTemperature;

/**
 * Brew Recipe Entity
 *
 * A coffee brewing recipe defines how to extract coffee from ground beans.
 * Each brewing method has specific requirements for grind size, temperature,
 * time, and ratio to achieve optimal extraction.
 *
 * This entity demonstrates:
 * - Two-phase validation (validate primitives, then create value objects)
 * - Method-specific business rules
 * - Single source of truth (business rules contain all validation logic)
 * - Rich domain model with meaningful value objects
 *
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⡼⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣼⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠰⡏⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢰⡇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢸⣷⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢿⣷⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⠀⠈⢻⣿⣄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠈⢆⠀⠀⠙⣿⣆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢧⠀⠀⠘⢿⣇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣸⡆⠀⠀⠘⣿⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣰⣿⠃⠀⠀⠀⣿⠇⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣼⣿⠃⠀⠀⠀⠀⡿⠁⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣿⡏⣀⣀⣀⠀⡜⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⣀⡤⠤⠒⠒⠋⠉⠉⠻⣧⠀⠀⠀⠈⠉⠁⠀⠀⠀⠢⢄⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⣾⣿⠀⠀⠀⠀⣀⣀⣀⣀⣤⣽⣦⣄⣀⣀⣀⣀⠀⠀⠀⠀⢹⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⣿⣿⣿⠷⠾⠿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡿⠶⠚⠀⠀⠀⠀⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⢿⣿⡏⠀⠀⠀⠀⠀⠀⠈⠉⠉⠉⠉⠉⠉⠀⠀⠀⠀⠀⠀⠀⣸⠛⠻⣷⠀⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠸⣿⣧⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢀⠃⠀⢠⣿⠇⠀⠀
 * ⠀⠀⠀⠀⠀⠀⠀⣹⣿⡆⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⢠⣎⣠⣴⠿⠃⠀⠀⠀
 * ⠀⢀⣠⠔⠒⠈⠉⠀⠹⣿⣄⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣠⠾⠛⠛⠉⠒⠢⣄⠀⠀
 * ⠀⣿⡁⠀⠀⠀⠀⠀⠀⠈⢻⣦⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⣀⣾⡃⠀⠀⠀⠀⠀⠀⠀⡟⠀
 * ⠀⠙⠻⣶⣀⠀⠀⠀⠀⠀⠀⠈⠙⠲⠦⣤⣄⣀⣀⣀⣤⣤⣾⣯⡵⠞⠋⠀⠀⠀  ⣀⠟⠀⠀⠀⠀
 * ⠀⠀⠀⠀⠉⠛⠻⠿⠿⠶⠶⠤⠤⠤⣄⣀⣀⣀⣀⣀⣀⣀⣀⡠⠤⠤⠤⠴⠖ ⠉⠀⠀⠀⠀⠀⠀
 */
class BrewRecipe
{
    private function __construct(
        private BrewMethod $method,
        private CoffeeAmount $coffeeAmount,
        private WaterAmount $waterAmount,
        private GrindSize $grindSize,
        private WaterTemperature $waterTemperature,
        private BrewTime $brewTime,
        private ?string $notes = null
    ) {
    }

    /**
     * Create a new brew recipe with full validation.
     *
     * Uses two-phase validation:
     * 1. Validate method-specific requirements (grind, temp, time, ratio)
     * 2. Create value objects (validates basic ranges internally)
     *
     * @param BrewMethod $method Brewing method (espresso, pour over, etc.)
     * @param float $coffeeGrams Coffee amount in grams (5-60g)
     * @param float $waterMilliliters Water amount in milliliters (25-1000ml)
     * @param GrindSize $grindSize Grind size (extra fine → extra coarse)
     * @param float $waterCelsius Water temperature in Celsius (0-100°C)
     * @param int $brewSeconds Brew time in seconds (10s - 24h)
     * @param string|null $notes Optional tasting notes or instructions
     *
     * @throws \InvalidArgumentException if any business rule is violated
     */
    public static function create(
        BrewMethod $method,
        float $coffeeGrams,
        float $waterMilliliters,
        GrindSize $grindSize,
        float $waterCelsius,
        int $brewSeconds,
        ?string $notes = null
    ): self {
        $violations = [];

        // Phase 1: Validate method-specific requirements
        // Note: Basic validation (ranges) is handled by value object constructors
        // Ratio validation is part of method-specific rules (each method has different ideal ratios)
        $methodRule = self::getMethodSpecificRule($method);
        if ($methodRule !== null) {
            $recipeData = new BrewRecipeData(
                grindSize: $grindSize,
                waterTemp: $waterCelsius,
                brewTime: $brewSeconds,
                coffee: $coffeeGrams,
                water: $waterMilliliters
            );

            if (!$methodRule->isSatisfiedBy($recipeData)) {
                $violations[] = $methodRule->getCode() . ': ' . $methodRule->getMessage();
            }
        }

        // If any violations, throw exception with all of them
        if (!empty($violations)) {
            throw new \InvalidArgumentException(
                'Brew recipe validation failed: ' . implode('; ', $violations)
            );
        }

        // Phase 2: Create value objects (validates basic ranges internally)
        $coffeeAmount = CoffeeAmount::fromGrams($coffeeGrams);
        $waterAmount = WaterAmount::fromMilliliters($waterMilliliters);
        $waterTemp = WaterTemperature::fromCelsius($waterCelsius);
        $brewTime = BrewTime::fromSeconds($brewSeconds);

        return new self($method, $coffeeAmount, $waterAmount, $grindSize, $waterTemp, $brewTime, $notes);
    }

    /**
     * Get the method-specific business rule for validation.
     */
    private static function getMethodSpecificRule(BrewMethod $method): ?BusinessRule
    {
        return match ($method) {
            BrewMethod::ESPRESSO => new EspressoRecipeRule(),
            BrewMethod::POUR_OVER => new PourOverRecipeRule(),
            BrewMethod::FRENCH_PRESS => new FrenchPressRecipeRule(),
            BrewMethod::COLD_BREW => new ColdBrewRecipeRule(),
            BrewMethod::AEROPRESS => new AeroPressRecipeRule(),
            BrewMethod::MOKA_POT => new MokaPotRecipeRule(),
        };
    }

    // Getters

    public function getMethod(): BrewMethod
    {
        return $this->method;
    }

    public function getCoffeeAmount(): CoffeeAmount
    {
        return $this->coffeeAmount;
    }

    public function getWaterAmount(): WaterAmount
    {
        return $this->waterAmount;
    }

    public function getGrindSize(): GrindSize
    {
        return $this->grindSize;
    }

    public function getWaterTemperature(): WaterTemperature
    {
        return $this->waterTemperature;
    }

    public function getBrewTime(): BrewTime
    {
        return $this->brewTime;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Calculate the brew ratio (water : coffee).
     *
     * Example: 300ml water / 20g coffee = 15 (ratio is 1:15)
     */
    public function getBrewRatio(): float
    {
        return $this->waterAmount->toMilliliters() / $this->coffeeAmount->toGrams();
    }

    /**
     * Get a human-readable summary of this recipe.
     */
    public function getSummary(): string
    {
        $ratio = number_format($this->getBrewRatio(), 1);

        return sprintf(
            "%s: %s coffee, %s water (1:%s ratio), %s grind, %s water, %s brew time",
            $this->method->getName(),
            $this->coffeeAmount,
            $this->waterAmount,
            $ratio,
            $this->grindSize->getName(),
            $this->waterTemperature,
            $this->brewTime
        );
    }

    /**
     * Update brewing notes (tasting notes, adjustments, etc.)
     */
    public function updateNotes(string $notes): void
    {
        $this->notes = $notes;
    }
}
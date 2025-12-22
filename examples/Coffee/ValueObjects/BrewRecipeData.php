<?php

namespace fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects;

/**
 * Brew Recipe Data - Data structure for validating brew recipes.
 *
 * This value object encapsulates all parameters needed to validate a brewing recipe.
 * It provides type safety and IDE autocomplete support for complex multi-field validations.
 *
 * Used by method-specific business rules (EspressoRecipeValid, FrenchPressRecipeValid, etc.)
 * to validate that all brewing parameters are appropriate for the chosen method.
 *
 * Why use this instead of arrays?
 * - IDE autocomplete for all properties
 * - Type checking at construction time
 * - Self-documenting code
 * - Immutable and safe to pass around
 * - Still works with single-parameter isSatisfiedBy()
 *
 * Example:
 * ```php
 * $data = new BrewRecipeData(
 *     grindSize: GrindSize::FINE,
 *     waterTemp: 93.0,
 *     brewTime: 27,
 *     coffee: 18.0,
 *     water: 36.0
 * );
 *
 * $rule = new EspressoRecipeRule();
 * $isValid = $rule->isSatisfiedBy($data); // Full IDE support!
 * ```
 */
final readonly class BrewRecipeData
{
    /**
     * Create brew recipe validation data.
     *
     * @param GrindSize $grindSize Coffee grind size (extra fine → extra coarse)
     * @param float $waterTemp Water temperature in Celsius (0-100°C)
     * @param int $brewTime Brew time in seconds
     * @param float $coffee Coffee amount in grams
     * @param float $water Water amount in milliliters
     */
    public function __construct(
        public GrindSize $grindSize,
        public float $waterTemp,
        public int $brewTime,
        public float $coffee,
        public float $water
    ) {
    }

    /**
     * Calculate the brew ratio (water : coffee).
     *
     * Example: 300ml water / 20g coffee = 15.0 (ratio is 1:15)
     */
    public function getRatio(): float
    {
        if ($this->coffee <= 0) {
            return 0.0;
        }

        return $this->water / $this->coffee;
    }

    /**
     * Create from array data.
     *
     * @param array{grindSize: GrindSize, waterTemp: float, brewTime: int, coffee: float, water: float} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            grindSize: $data['grindSize'],
            waterTemp: (float)$data['waterTemp'],
            brewTime: (int)$data['brewTime'],
            coffee: (float)$data['coffee'],
            water: (float)$data['water']
        );
    }

    /**
     * Convert to array representation.
     *
     * @return array{grindSize: GrindSize, waterTemp: float, brewTime: int, coffee: float, water: float}
     */
    public function toArray(): array
    {
        return [
            'grindSize' => $this->grindSize,
            'waterTemp' => $this->waterTemp,
            'brewTime' => $this->brewTime,
            'coffee' => $this->coffee,
            'water' => $this->water,
        ];
    }
}
<?php

namespace fragoe\DDDBusinessRules;

/**
 * Abstract base class for business rules.
 *
 * Provides default implementations of getCode() and getMessage() using the class name.
 * Subclasses should override getCode() and getMessage() for better violation reporting.
 */
abstract class CompositeBusinessRule implements BusinessRule
{
    /**
     * Human-readable message describing this business rule.
     *
     * @var null|string
     */
    protected const MESSAGE = null;

    /**
     * Unique code that identifies this business rule.
     *
     * @var null|int|string
     */
    protected const CODE = null;

    /**
     * Get a human-readable message describing this business rule.
     *
     * Default implementation:
     * Checks if the MESSAGE constant is set and if so, returns it.
     * Otherwise, it converts the class name into human-readable format
     * (class ProductNameLength → "Product Name Length").
     *
     * Override the MESSAGE constant or this method in subclasses for
     * custom messages.
     *
     * **Example:**
     * - "Product Name Length"
     *
     * @return string
     */
    public function getMessage(): string
    {
        if (static::MESSAGE !== null) {
            return static::MESSAGE;
        }

        $className = (new \ReflectionClass($this))->getShortName();
        return $this->classNameToHumanReadable($className);
    }

    /**
     * Convert a class name into human-readable format.
     */
    private function classNameToHumanReadable(string $className): string
    {
        // Insert spaces before uppercase letters
        $readable = preg_replace('/(?<!^)[A-Z]/', ' $0', $className);
        return $readable ?? $className;
    }

    /**
     * Get the unique code that identifies this business rule.
     *
     * The code should be unique and consistent within your domain to
     * allow clients to handle specific rules programmatically.
     *
     * Default implementation:
     * Checks if the CODE constant is set and if so, returns it.
     * Otherwise, it converts the class name into dot notation
     * (ProductNameLength → "product.name.length").
     *
     * Override the CODE constant or this method in subclasses for
     * custom codes.
     *
     * **Examples:**
     * - "product.name.length" (human-readable)
     * - "c131356c-725e-4108-a153-e0fcc93fbf96" (UUID)
     * - 1001 (numeric code)
     *
     * @return int|string
     */
    public function getCode(): int|string
    {
        if (static::CODE !== null) {
            return static::CODE;
        }

        $className = (new \ReflectionClass($this))->getShortName();
        return $this->classNameToDotNotation($className);
    }

    /**
     * Convert a class name to dot notation.
     *
     * Example: ProductNameLength → product.name.length
     */
    private function classNameToDotNotation(string $className): string
    {
        // Insert dots before uppercase letters, convert to lowercase
        $dotted = preg_replace('/(?<!^)[A-Z]/', '.$0', $className);
        return strtolower($dotted ?? $className);
    }

    /**
     * Combine this rule with another using AND logic.
     *
     * @param BusinessRule $other The other business rule to combine with.
     *
     * @return AndBusinessRule Returns a new AND business rule, satisfied only when both rules are satisfied.
     */
    public function and(BusinessRule $other): AndBusinessRule
    {
        return new AndBusinessRule($this, $other);
    }

    /**
     * Combine this rule with another using OR logic.
     *
     * @param BusinessRule $other The other business rule to combine with.
     *
     * @return OrBusinessRule Returns a new OR business rule, satisfied when at least one rule is satisfied.
     */
    public function or(BusinessRule $other): OrBusinessRule
    {
        return new OrBusinessRule($this, $other);
    }

    /**
     * Combine this rule with another using XOR logic.
     *
     * @param BusinessRule $other The other business rule to combine with.
     *
     * @return XorBusinessRule Returns a new XOR business rule, satisfied when exactly one,
     *  and only one, rule is satisfied.
     */
    public function xor(BusinessRule $other): XorBusinessRule
    {
        return new XorBusinessRule( // (A AND NOT B) OR (NOT A AND B)
            new AndBusinessRule($this, new NotBusinessRule($other)),
            new AndBusinessRule(new NotBusinessRule($this), $other),
        );
    }

    /**
     * Negate this rule using NOT logic.
     *
     * @return NotBusinessRule Returns a new NOT business rule, satisfied when this rule is not satisfied.
     */
    public function not(): NotBusinessRule
    {
        return new NotBusinessRule($this);
    }
}
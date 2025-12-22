<?php

namespace fragoe\DDDBusinessRules;

/**
 * **Business rule** interface following the specification pattern.
 *
 * All business rules are composable by nature. This interface ensures every rule
 * can be combined with others using AND, OR, NOT, and XOR logic.
 *
 * Additionally, business rules provide metadata (code and message) for logging,
 * reporting, and API responses.
 *
 * @link https://en.wikipedia.org/wiki/Specification_pattern
 */
interface BusinessRule
{
    /**
     * Get a human-readable message describing this business rule.
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Get the unique code that identifies this business rule.
     *
     * The code should be unique and consistent within your domain to
     * allow clients to handle specific rules programmatically.
     *
     * **Examples:**
     * - "product.name.invalid_length" (human-readable code)
     * - "c131356c-725e-4108-a153-e0fcc93fbf96" (UUID)
     * - 1001 (numeric code)
     *
     * @return int|string
     */
    public function getCode();

    /**
     * Check if the value satisfies the business rule.
     *
     * @param  mixed $value The value to check.
     * @return bool True if the rule is satisfied, false otherwise.
     */
    public function isSatisfiedBy($value): bool;

    /**
     * Combine this rule with another using AND logic.
     *
     * @param BusinessRule $other The other business rule to combine with.
     *
     * @return BusinessRule Returns a new rule satisfied only when both rules are satisfied.
     */
    public function and(BusinessRule $other): BusinessRule;

    /**
     * Combine this rule with another using OR logic.
     *
     * @param BusinessRule $other The other business rule to combine with.
     *
     * @return BusinessRule Returns a new rule satisfied when at least one rule is satisfied.
     */
    public function or(BusinessRule $other): BusinessRule;

    /**
     * Combine this rule with another using XOR logic.
     *
     * @param BusinessRule $other The other business rule to combine with.
     *
     * @return BusinessRule Returns a new rule satisfied when exactly one, and only one, rule is satisfied.
     */
    public function xor(BusinessRule $other): BusinessRule;

    /**
     * Negate this rule using NOT logic.
     *
     * @return BusinessRule Returns a new rule satisfied when this rule is NOT satisfied.
     */
    public function not(): BusinessRule;
}
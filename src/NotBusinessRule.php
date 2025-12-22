<?php

namespace fragoe\DDDBusinessRules;

/**
 * Represents: NOT business rule
 * Returns TRUE if the inner business rule returns FALSE.
 */
class NotBusinessRule extends CompositeBusinessRule
{
    /**
     * Create a new NOT business rule.
     *
     * @param BusinessRule $businessRule The business rule to negate.
     */
    public function __construct(private BusinessRule $businessRule) {}

    /**
     * Checks whether the business rule is satisfied by a given value.
     *
     * @param mixed $value The value to check against the business rule.
     *
     * @return bool Returns true if the business rule is NOT satisfied, false otherwise.
     */
    public function isSatisfiedBy($value): bool
    {
        return !$this->businessRule->isSatisfiedBy($value);
    }

    public function getCode(): string
    {
        return 'NOT(' . $this->businessRule->getCode() . ')';
    }

    public function getMessage(): string
    {
        return 'Must NOT satisfy: ' . $this->businessRule->getMessage();
    }
}
<?php

namespace fragoe\DDDBusinessRules;

/**
 * Represents: businessRule1 OR businessRule2 OR businessRule3...
 * Returns TRUE if ANY business rule is satisfied.
 */
class OrBusinessRule extends CompositeBusinessRule
{
    private array $businessRules;

    /**
     * Create a new OR business rule.
     *
     * @param BusinessRule $businessRule The first business rule to combine with.
     * @param BusinessRule ...$moreBusinessRules Additional business rules to combine with.
     */
    public function __construct(BusinessRule $businessRule, BusinessRule ...$moreBusinessRules)
    {
        $this->businessRules = array_merge([$businessRule], $moreBusinessRules);
    }

    /**
     * Checks whether at least one business rule is satisfied by the given value.
     *
     * @param mixed $value The value to check.
     *
     * @return bool Returns true if at least one business rule is satisfied, false otherwise.
     */
    public function isSatisfiedBy($value): bool
    {
        foreach ($this->businessRules as $businessRule) {
            if ($businessRule->isSatisfiedBy($value)) {
                return true; // Short-circuit: one passed, result is true
            }
        }

        return false; // None satisfied
    }

    public function getCode(): string
    {
        $codes = array_map(fn(BusinessRule $rule) => $rule->getCode(), $this->businessRules);
        return '(' . implode(' OR ', $codes) . ')';
    }

    public function getMessage(): string
    {
        $messages = array_map(fn(BusinessRule $rule) => $rule->getMessage(), $this->businessRules);
        return 'At least one of the following must be satisfied: ' . implode(', ', $messages);
    }
}
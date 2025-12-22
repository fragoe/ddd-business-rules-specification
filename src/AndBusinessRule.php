<?php

namespace fragoe\DDDBusinessRules;

/**
 * Represents: businessRule1 AND businessRule2 AND businessRule3...
 * Returns TRUE only if ALL business rules are satisfied.
 */
class AndBusinessRule extends CompositeBusinessRule
{
    private array $businessRules;

    /**
     * Create a new AND business rule.
     *
     * @param BusinessRule $businessRule The first business rule to combine with.
     * @param BusinessRule ...$moreBusinessRules Additional business rules to combine with.
     */
    public function __construct(BusinessRule $businessRule, BusinessRule ...$moreBusinessRules)
    {
        $this->businessRules = array_merge([$businessRule], $moreBusinessRules);
    }

    /**
     * Checks whether all business rules are satisfied by the given value.
     *
     * @param mixed $value The value to check.
     *
     * @return bool Returns true if all business rules are satisfied, false otherwise.
     */
    public function isSatisfiedBy($value): bool
    {
        foreach ($this->businessRules as $businessRule) {
            if (!$businessRule->isSatisfiedBy($value)) {
                return false; // Short-circuit: one failed, all fail
            }
        }

        return true; // All business rules satisfied
    }

    public function getCode(): string
    {
        $codes = array_map(fn(BusinessRule $rule) => $rule->getCode(), $this->businessRules);
        return '(' . implode(' AND ', $codes) . ')';
    }

    public function getMessage(): string
    {
        $messages = array_map(fn(BusinessRule $rule) => $rule->getMessage(), $this->businessRules);
        return 'All of the following must be satisfied: ' . implode(', ', $messages);
    }
}
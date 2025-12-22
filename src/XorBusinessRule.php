<?php

namespace fragoe\DDDBusinessRules;

/**
 * Represents: businessRule1 XOR businessRule2 XOR businessRule3...
 * Returns TRUE if EXACTLY ONE business rule is satisfied.
 */
class XorBusinessRule extends CompositeBusinessRule
{
    private array $businessRules;

    /**
     * Create a new XOR business rule.
     *
     * @param BusinessRule $businessRule The first business rule to combine with.
     * @param BusinessRule ...$moreBusinessRules Additional business rules to combine with.
     */
    public function __construct(BusinessRule $businessRule, BusinessRule ...$moreBusinessRules)
    {
        $this->businessRules = array_merge([$businessRule], $moreBusinessRules);
    }

    /**
     * Checks whether exactly one, and only one, business rule is satisfied by the given value.
     *
     * @param mixed $value The value to check.
     *
     * @return bool Returns true when exactly one, and only one, business rule is satisfied, false otherwise.
     */
    public function isSatisfiedBy($value): bool
    {
        $result = false;
        foreach ($this->businessRules as $businessRule) {
            if ($businessRule->isSatisfiedBy($value)) {
                if ($result) {
                    return false; // More than one satisfied; stop here
                }
                $result = true; // One satisfied
            }
        }

        return $result;
    }

    public function getCode(): string
    {
        $codes = array_map(fn(BusinessRule $rule) => $rule->getCode(), $this->businessRules);
        return '(' . implode(' XOR ', $codes) . ')';
    }

    public function getMessage(): string
    {
        $messages = array_map(fn(BusinessRule $rule) => $rule->getMessage(), $this->businessRules);
        return 'Only one of the following must be satisfied: ' . implode(', ', $messages);
    }
}
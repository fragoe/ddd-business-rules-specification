<?php


use fragoe\DDDBusinessRules\CompositeBusinessRule;

class ProductDescriptionLength extends CompositeBusinessRule
{
    protected const MAX_LENGTH = 8192;

    protected const MESSAGE = 'Product description must be less than ' . self::MAX_LENGTH + 1 .  ' characters long.';

    protected const CODE = 'product.description.length';

    public function isSatisfiedBy($value): bool
    {
        if ($value === null) {
            return true; // Description is optional
        }

        if (!is_string($value)) {
            return false; // Must be a string
        }

        return mb_strlen($value) <= self::MAX_LENGTH;
    }
}
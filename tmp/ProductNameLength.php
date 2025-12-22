<?php


use fragoe\DDDBusinessRules\CompositeBusinessRule;

final class ProductNameLength extends CompositeBusinessRule
{
    protected const MESSAGE = 'Product name must be between 3 and 50 characters long.';

    protected const CODE = 'product.name.length';

    public function isSatisfiedBy($value): bool
    {
        if ($value === null || !is_string($value)) {
            return false;
        }

        $length = strlen($value);
        return $length >= 3 && $length <= 50;
    }
}
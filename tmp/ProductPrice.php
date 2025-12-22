<?php


use fragoe\DDDBusinessRules\CompositeBusinessRule;

class ProductPrice extends CompositeBusinessRule
{
    protected const MIN_PRICE = 0;

    protected const MESSAGE = 'Product price must be greater than or equal to ' . self::MIN_PRICE . '.';

    protected const CODE = 'product.price';

    public function isSatisfiedBy($value): bool
    {
        if ($value === null || !is_numeric($value) || $value < self::MIN_PRICE) {
            return false;
        }
        return true;
    }
}
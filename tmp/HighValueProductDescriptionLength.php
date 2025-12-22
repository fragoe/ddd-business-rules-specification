<?php


class HighValueProductDescriptionLength extends ProductDescriptionLength
{
    protected const MIN_LENGTH = 100;

    protected const MESSAGE = 'Product description must at least ' . self::MIN_LENGTH - 1 .  ' and less than ' . self::MAX_LENGTH + 1 . ' characters long.';

    protected const CODE = 'product.high_value.description.length';

    public function isSatisfiedBy($value): bool
    {
        if ($value === null || !is_string($value)) {
            return false;
        }

        return strlen($value) >= 100;
    }
}
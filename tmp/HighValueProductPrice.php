<?php


class HighValueProductPrice extends ProductPrice
{
    protected const THRESHOLD_PRICE = 1000;



    public function isSatisfiedBy($value): bool
    {
        if (!parent::isSatisfiedBy($value)) {
            return false;
        }

        if ($value < self::THRESHOLD_PRICE) {
            return false;
        }

        return true;
    }
}
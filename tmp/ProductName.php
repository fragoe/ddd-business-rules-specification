<?php


class ProductName
{
    private function __construct(private string $value)
    {
        $businessRule = new ProductNameLength();
        if (!$businessRule->isSatisfiedBy($this->value)) {
            throw new \InvalidArgumentException($businessRule->getMessage());
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
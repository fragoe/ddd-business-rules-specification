# Validation, Business Rules, and Value Objects in DDD

## The Challenge

In Domain-Driven Design, we have competing requirements:

1. **User Experience:** API endpoints should return **all validation errors at once**, not just the first one
2. **DDD Value Objects:** Should enforce invariants by **throwing exceptions** when invalid
3. **Single Source of Truth:** Validation logic should **not be duplicated** across rules and value objects

## The Problem with Naive Approaches

### Approach 1: Only Value Objects ❌

```php
// Value objects throw on first error
class ProductName {
    public function __construct(private string $value) {
        if (mb_strlen($value) < 3) {
            throw new \InvalidArgumentException('Name too short');
        }
    }
}

class Price {
    public function __construct(private float $amount) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Price must be positive');
        }
    }
}

// Usage
try {
    $product = new Product(
        name: new ProductName($dto->name),    // ❌ Throws here
        price: new Price($dto->price),        // Never reached if name fails
        slug: new Slug($dto->slug)            // Never reached
    );
} catch (\InvalidArgumentException $e) {
    // Only know about the FIRST error!
    return ['error' => $e->getMessage()];
}
```

**Problem:** User only sees one error at a time. Must fix and retry repeatedly.

### Approach 2: Duplicate Logic in Rules and Value Objects ❌

```php
// Business Rule
class ProductNameValid extends CompositeBusinessRule {
    public function isSatisfiedBy($value): bool {
        return is_string($value)
            && mb_strlen($value) >= 3
            && mb_strlen($value) <= 128;  // Logic defined here
    }
}

// Value Object
class ProductName {
    public function __construct(private string $value) {
        if (!is_string($value)
            || mb_strlen($value) < 3
            || mb_strlen($value) > 128) {  // Same logic duplicated!
            throw new \InvalidArgumentException('Invalid name');
        }
    }
}
```

**Problem:** Violates DRY principle. Logic is duplicated. Changes must be made in two places.

## ✅ The Solution: Two-Phase Validation with Single Source of Truth

### Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│ 1. API Request (primitives: strings, numbers)          │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 2. Business Rules Validation                           │
│    ⚡ SOURCE OF TRUTH                                   │
│    ✓ ProductNameValid::isSatisfiedBy()                 │
│    ✓ ProductPricePositive::isSatisfiedBy()             │
│    ✓ ProductSlugValid::isSatisfiedBy()                 │
│    → Collect ALL violations                            │
└────────────────┬────────────────────────────────────────┘
                 │
        If violations exist ──────┐
                 │                │
                 │                ▼
                 │    ┌───────────────────────────────┐
                 │    │ Return 422 with ALL errors    │
                 │    │ {                             │
                 │    │   "errors": [                 │
                 │    │     {"code": "name.invalid"}, │
                 │    │     {"code": "price.negative"}│
                 │    │   ]                           │
                 │    │ }                             │
                 │    └───────────────────────────────┘
                 │
         If valid│
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 3. Create Value Objects                                │
│    ⚡ DELEGATE TO BUSINESS RULES                        │
│    ProductName::fromString()                           │
│       → uses ProductNameValid                          │
│    Price::fromFloat()                                  │
│       → uses ProductPricePositive                      │
│    (Should never throw if validation passed)           │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 4. Create Entity (domain logic)                        │
│    Product::create(valueObjects...)                    │
│    → Always receives valid value objects               │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 5. Persist to database                                 │
└─────────────────────────────────────────────────────────┘
```

### Key Principles

1. **Business Rules = Source of Truth** - All validation logic lives here
2. **Value Objects = Delegate** - Call business rules, don't duplicate logic
3. **Two-Phase Validation** - Validate all at once, then create value objects
4. **Fail Fast** - Value objects still throw (last line of defense)
5. **No Duplication** - Logic defined once, reused everywhere

## Implementation

### 1. Business Rules (Source of Truth)

```php
namespace fragoe\DDDBusinessRules\Examples\Product\Rule;

use fragoe\DDDBusinessRules\CompositeBusinessRule;

class ProductNameValid extends CompositeBusinessRule
{
    public function isSatisfiedBy($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);
        return $length >= 3 && $length <= 128;
    }

    public function getCode(): string
    {
        return 'product.name.valid';
    }

    public function getDescription(): string
    {
        return 'Product name must be between 3 and 128 characters';
    }
}

class ProductPricePositive extends CompositeBusinessRule
{
    public function isSatisfiedBy($value): bool
    {
        return is_numeric($value) && $value > 0;
    }

    public function getCode(): string
    {
        return 'product.price.positive';
    }

    public function getDescription(): string
    {
        return 'Product price must be greater than zero';
    }
}

class ProductSlugValid extends CompositeBusinessRule
{
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public function isSatisfiedBy($value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $length = mb_strlen($value);
        if ($length < 2 || $length > 100) {
            return false;
        }

        return preg_match(self::PATTERN, $value) === 1;
    }

    public function getCode(): string
    {
        return 'product.slug.valid';
    }

    public function getDescription(): string
    {
        return 'Product slug must be lowercase letters, numbers, and dashes';
    }
}
```

### 2. Value Objects (Delegate to Business Rules)

```php
namespace fragoe\DDDBusinessRules\Examples\Product\ValueObject;

use fragoe\DDDBusinessRules\Examples\Product\Rule\*;

final readonly class ProductName
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        // Delegate to business rule - SINGLE SOURCE OF TRUTH
        $rule = new ProductNameValid();

        if (!$rule->isSatisfiedBy($value)) {
            throw new \InvalidArgumentException(
                $rule->getDescription()  // Reuse description from rule
            );
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(ProductName $other): bool
    {
        return $this->value === $other->value;
    }
}

final readonly class Price
{
    private function __construct(private float $amount) {}

    public static function fromFloat(float $amount): self
    {
        // Delegate to business rule
        $rule = new ProductPricePositive();

        if (!$rule->isSatisfiedBy($amount)) {
            throw new \InvalidArgumentException(
                $rule->getDescription()
            );
        }

        return new self($amount);
    }

    public function toFloat(): float
    {
        return $this->amount;
    }

    public function isHighValue(float $threshold = 1000.0): bool
    {
        return $this->amount > $threshold;
    }
}

final readonly class Slug
{
    private function __construct(private string $value) {}

    public static function fromString(string $value): self
    {
        // Delegate to business rule
        $rule = new ProductSlugValid();

        if (!$rule->isSatisfiedBy($value)) {
            throw new \InvalidArgumentException(
                $rule->getDescription()
            );
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}

final readonly class ProductDescription
{
    private function __construct(private ?string $value) {}

    public static function fromString(?string $value): self
    {
        // Delegate to business rule
        $rule = new ProductDescriptionValid();

        if (!$rule->isSatisfiedBy($value)) {
            throw new \InvalidArgumentException(
                $rule->getDescription()
            );
        }

        return new self($value);
    }

    public function toString(): ?string
    {
        return $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '';
    }

    public function length(): int
    {
        return $this->value ? mb_strlen($this->value) : 0;
    }
}
```

### 3. Application Service (Two-Phase Validation)

```php
namespace fragoe\DDDBusinessRules\Examples\Product\Application;

use fragoe\DDDBusinessRules\Examples\Product\Entity\Product;
use fragoe\DDDBusinessRules\Examples\Product\ValueObject\*;
use fragoe\DDDBusinessRules\Examples\Product\Rule\*;
use fragoe\DDDBusinessRulesValidation\BusinessRuleValidator;
use fragoe\DDDBusinessRulesValidation\DomainRuleViolationException;

class CreateProductService
{
    public function __construct(
        private BusinessRuleValidator $validator
    ) {}

    public function execute(CreateProductDto $dto): Product
    {
        // ===================================================================
        // PHASE 1: Validate primitives with business rules
        // Collect ALL violations at once for better UX
        // ===================================================================

        $violations = $this->validateDto($dto);

        if ($violations->hasViolations()) {
            // Return all errors to the user at once
            throw new DomainRuleViolationException($violations);
        }

        // ===================================================================
        // PHASE 2: Create value objects
        // Uses same business rules internally via delegation
        // Should NEVER throw if validation passed correctly
        // ===================================================================

        try {
            $name = ProductName::fromString($dto->name);
            $description = ProductDescription::fromString($dto->description);
            $price = Price::fromFloat($dto->price);
            $slug = Slug::fromString($dto->slug);

        } catch (\InvalidArgumentException $e) {
            // This should NEVER happen if business rules are correct
            // Log this as it indicates a bug in our validation
            throw new \LogicException(
                'Value object creation failed after validation passed: ' . $e->getMessage(),
                0,
                $e
            );
        }

        // ===================================================================
        // PHASE 3: Create entity with validated value objects
        // Entity always receives valid state
        // ===================================================================

        return Product::create($name, $description, $price, $slug);
    }

    private function validateDto(CreateProductDto $dto): DomainRuleViolationList
    {
        $allViolations = new DomainRuleViolationList();

        // Validate individual fields
        $allViolations->addAll(
            $this->validator->validate(
                $dto->name,
                new ProductNameValid(),
                propertyPath: 'name'
            )
        );

        $allViolations->addAll(
            $this->validator->validate(
                $dto->description,
                new ProductDescriptionValid(),
                propertyPath: 'description'
            )
        );

        $allViolations->addAll(
            $this->validator->validate(
                $dto->price,
                new ProductPricePositive(),
                propertyPath: 'price'
            )
        );

        $allViolations->addAll(
            $this->validator->validate(
                $dto->slug,
                new ProductSlugValid(),
                propertyPath: 'slug'
            )
        );

        // Validate cross-field business rules
        $allViolations->addAll(
            $this->validator->validate(
                [
                    'price' => $dto->price,
                    'description' => $dto->description
                ],
                new HighValueProductDescriptionValid()
            )
        );

        return $allViolations;
    }
}
```

### 4. Entity (Uses Value Objects)

```php
namespace fragoe\DDDBusinessRules\Examples\Product\Entity;

use fragoe\DDDBusinessRules\Examples\Product\ValueObject\*;

class Product
{
    private function __construct(
        private ProductName $name,
        private ProductDescription $description,
        private Price $price,
        private Slug $slug,
        private \DateTimeImmutable $createdAt
    ) {}

    public static function create(
        ProductName $name,
        ProductDescription $description,
        Price $price,
        Slug $slug
    ): self {
        // Entity-level business rules (cross-value-object validation)
        // This is already validated by HighValueProductDescriptionValid
        // but enforced here as well (defense in depth)
        if ($price->isHighValue() && $description->length() < 10) {
            throw new \DomainException(
                'High-value products require detailed descriptions'
            );
        }

        return new self(
            $name,
            $description,
            $price,
            $slug,
            new \DateTimeImmutable()
        );
    }

    // Getters return value objects, not primitives
    public function getName(): ProductName { return $this->name; }
    public function getDescription(): ProductDescription { return $this->description; }
    public function getPrice(): Price { return $this->price; }
    public function getSlug(): Slug { return $this->slug; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}
```

### 5. Controller/API Layer

```php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ProductController
{
    public function __construct(
        private CreateProductService $createProductService
    ) {}

    public function create(Request $request): JsonResponse
    {
        $dto = CreateProductDto::fromRequest($request);

        try {
            $product = $this->createProductService->execute($dto);

            return new JsonResponse([
                'id' => $product->getId(),
                'name' => $product->getName()->toString(),
                'slug' => $product->getSlug()->toString()
            ], 201);

        } catch (DomainRuleViolationException $e) {
            // Return ALL violations to the client
            return new JsonResponse([
                'errors' => $e->getViolations()->toArray()
            ], 422);
        }
    }
}
```

### API Response Example

When multiple fields are invalid, the API returns all errors at once:

```json
{
    "errors": [
        {
            "code": "product.name.valid",
            "message": "Product name must be between 3 and 128 characters",
            "propertyPath": "name",
            "value": "AB"
        },
        {
            "code": "product.price.positive",
            "message": "Product price must be greater than zero",
            "propertyPath": "price",
            "value": -10
        },
        {
            "code": "product.slug.valid",
            "message": "Product slug must be lowercase letters, numbers, and dashes",
            "propertyPath": "slug",
            "value": "Invalid Slug!"
        },
        {
            "code": "product.high-value-description.required",
            "message": "Products over 1000€ require a description of at least 10 characters",
            "value": {
                "price": 1500,
                "description": "Short"
            }
        }
    ]
}
```

## Complex Business Rules

### Cross-Field Validation

Some business rules span multiple fields:

```php
class HighValueProductDescriptionValid extends CompositeBusinessRule
{
    public function __construct(
        private float $priceThreshold = 1000.0,
        private int $minDescriptionLength = 10
    ) {}

    public function isSatisfiedBy($data): bool
    {
        // Expects array with 'price' and 'description'
        if (!is_array($data)) {
            return false;
        }

        $price = $data['price'] ?? 0;
        $description = $data['description'] ?? '';

        // If not high value, description can be anything
        if ($price <= $this->priceThreshold) {
            return true;
        }

        // High value product - description must be detailed
        return is_string($description)
            && mb_strlen($description) >= $this->minDescriptionLength
            && mb_strlen($description) <= 8192;
    }

    public function getCode(): string
    {
        return 'product.high-value-description.required';
    }

    public function getDescription(): string
    {
        return "Products over {$this->priceThreshold}€ require a description of at least {$this->minDescriptionLength} characters";
    }
}
```

Usage in application service:

```php
// Validate cross-field rule
$violations = $this->validator->validate(
    [
        'price' => $dto->price,
        'description' => $dto->description
    ],
    new HighValueProductDescriptionValid()
);
```

## Benefits Summary

### ✅ Single Source of Truth
- Business rules contain all validation logic
- Value objects delegate to business rules
- Change logic in one place, everywhere updates

### ✅ Excellent User Experience
- Collect ALL violations before responding
- Users see all errors at once
- No frustrating "fix one error, find another" loop

### ✅ DDD Compliance
- Value objects still enforce invariants
- Entities always have valid state
- Proper layering and separation of concerns

### ✅ Type Safety
- Value objects provide type safety
- Can't accidentally use invalid primitives
- IDE autocomplete for value object methods

### ✅ Testability
- Business rules easy to test in isolation
- Value objects test delegation
- Application service tests complete workflow

### ✅ Reusability
- Same business rules used for:
  - Input validation (collect violations)
  - Value object creation (enforce invariants)
  - Database queries (Doctrine translator)
  - Documentation (analyzer)

## Common Pitfalls to Avoid

### ❌ Don't Duplicate Logic

```php
// BAD - Logic duplicated
class ProductNameValid extends CompositeBusinessRule {
    public function isSatisfiedBy($value): bool {
        return mb_strlen($value) >= 3;  // Logic here
    }
}

class ProductName {
    public function __construct(private string $value) {
        if (mb_strlen($value) < 3) {  // AND here (duplicated!)
            throw new \InvalidArgumentException('...');
        }
    }
}

// GOOD - Single source of truth
class ProductName {
    public static function fromString(string $value): self {
        $rule = new ProductNameValid();  // Delegates to rule
        if (!$rule->isSatisfiedBy($value)) {
            throw new \InvalidArgumentException($rule->getDescription());
        }
        return new self($value);
    }
}
```

### ❌ Don't Skip Phase 1 Validation

```php
// BAD - Create value objects directly (only first error)
try {
    $name = ProductName::fromString($dto->name);  // Throws on error
    $price = Price::fromFloat($dto->price);       // Never reached if name invalid
} catch (\InvalidArgumentException $e) {
    return ['error' => $e->getMessage()];  // Only ONE error
}

// GOOD - Validate all first (collect all errors)
$violations = $validator->validateAll($dto);
if ($violations->hasViolations()) {
    return ['errors' => $violations->toArray()];  // ALL errors
}
// Only create value objects after validation passes
$name = ProductName::fromString($dto->name);
```

### ❌ Don't Throw Different Exceptions

```php
// BAD - Value object throws different exception than rule message
class ProductNameValid extends CompositeBusinessRule {
    public function getDescription(): string {
        return 'Name must be 3-128 characters';
    }
}

class ProductName {
    public static function fromString(string $value): self {
        if (mb_strlen($value) < 3) {
            throw new \InvalidArgumentException('Name too short');  // Different message!
        }
    }
}

// GOOD - Reuse rule description
class ProductName {
    public static function fromString(string $value): self {
        $rule = new ProductNameValid();
        if (!$rule->isSatisfiedBy($value)) {
            throw new \InvalidArgumentException(
                $rule->getDescription()  // Same message everywhere
            );
        }
        return new self($value);
    }
}
```

## Integration with Validation Package

The validation package (to be created) provides:

```php
// BusinessRuleValidator
class BusinessRuleValidator {
    public function validate(
        mixed $value,
        BusinessRule $rule,
        ?string $propertyPath = null
    ): DomainRuleViolationList;
}

// DomainRuleViolation
class DomainRuleViolation {
    public function getCode(): string;
    public function getMessage(): string;
    public function getPropertyPath(): ?string;
    public function getValue(): mixed;
}

// DomainRuleViolationList
class DomainRuleViolationList {
    public function add(DomainRuleViolation $violation): void;
    public function addAll(DomainRuleViolationList $violations): void;
    public function hasViolations(): bool;
    public function getViolations(): array;
    public function toArray(): array;
}

// DomainRuleViolationException
class DomainRuleViolationException extends \RuntimeException {
    public function __construct(DomainRuleViolationList $violations);
    public function getViolations(): DomainRuleViolationList;
}
```

## Conclusion

The two-phase validation approach with business rules as the single source of truth provides:

1. **Great UX** - All errors returned at once
2. **No duplication** - Logic defined once, reused everywhere
3. **DDD compliance** - Value objects enforce invariants, entities always valid
4. **Type safety** - Value objects prevent primitive obsession
5. **Testability** - Each layer tests independently
6. **Maintainability** - Change logic in one place

This pattern balances the needs of user experience, domain modeling, and software engineering best practices.

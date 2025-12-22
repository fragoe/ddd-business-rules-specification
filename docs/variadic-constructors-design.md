# Design Decision: Variadic Constructors for Composite Rules

## Decision

All composite business rule classes (`AndBusinessRule`, `OrBusinessRule`, `XorBusinessRule`) accept variadic constructor parameters, allowing them to combine multiple rules at construction time.

```php
class AndBusinessRule extends CompositeBusinessRule {
    public function __construct(
        BusinessRule $businessRule,
        BusinessRule ...$moreBusinessRules
    ) {
        $this->businessRules = array_merge([$businessRule], $moreBusinessRules);
    }
}
```

This means developers can compose rules in two ways:

**1. Fluent API (Recommended)**
```php
$combined = $rule1
    ->and($rule2)
    ->and($rule3)
    ->and($rule4);
```

**2. Variadic Constructor**
```php
$combined = new AndBusinessRule($rule1, $rule2, $rule3, $rule4);
```

## Rationale

### Consistency Across Composite Rules

The primary driver for this decision is **consistency**. XOR has a legitimate use case for variadic constructors:

```php
// XOR: "Exactly ONE of these payment methods must be selected"
new XorBusinessRule(
    new PaymentByCreditCard(),
    new PaymentByPayPal(),
    new PaymentByCrypto(),
    new PaymentByBankTransfer()
);
```

Expressing "exactly one of N" with chained `.xor()` calls is semantically awkward:

```php
// Awkward: reads as nested XOR operations
$creditCard->xor($paypal)->xor($crypto)->xor($bank);
```

**Given that XOR needs variadic constructors, AND and OR should match for consistency.** Having different constructor signatures would create confusion:

```php
// ❌ Inconsistent (confusing!)
new AndBusinessRule($a, $b);           // Binary only
new OrBusinessRule($a, $b);            // Binary only
new XorBusinessRule($a, $b, $c, $d);   // Variadic (special case)
```

```php
// ✅ Consistent (predictable!)
new AndBusinessRule($a, $b, $c, $d);   // Variadic
new OrBusinessRule($a, $b, $c, $d);    // Variadic
new XorBusinessRule($a, $b, $c, $d);   // Variadic
```

### Benefits of Variadic Constructors

**1. Dynamic Rule Lists**

When working with rules loaded from configuration, database, or computed dynamically:

```php
// Rules from database
$eligibilityRules = $this->ruleRepository->findByType('eligibility');
$combined = new AndBusinessRule(...$eligibilityRules);

// Computed rules
$validationRules = array_map(
    fn($field) => $this->createValidationRule($field),
    $requiredFields
);
$validator = new AndBusinessRule(...$validationRules);
```

**2. Cleaner Syntax for Many Rules**

```php
// Variadic: flat and readable
new AndBusinessRule($a, $b, $c, $d, $e, $f);

// Fluent: also readable
$a->and($b)->and($c)->and($d)->and($e)->and($f);

// Binary constructor: deeply nested (avoided by our decision)
new AndBusinessRule($a, new AndBusinessRule($b, new AndBusinessRule($c, ...)));
```

**3. Flatter Object Graph**

```php
// Variadic: single AndBusinessRule with 4 children
new AndBusinessRule($a, $b, $c, $d);
// Structure: AND[$a, $b, $c, $d]

// Binary fluent: nested AndBusinessRules
$a->and($b)->and($c)->and($d);
// Structure: AND[$a, AND[$b, AND[$c, $d]]]
```

The variadic approach creates fewer objects and a simpler structure for debugging.

## Alternatives Considered

### Alternative 1: Binary Constructors Only

Force developers to always use the fluent API:

```php
class AndBusinessRule extends CompositeBusinessRule {
    public function __construct(
        private BusinessRule $left,
        private BusinessRule $right
    ) {}
}

// Only way to compose
$combined = $rule1->and($rule2)->and($rule3);
```

**Rejected because:**
- Inconsistent with XOR (which needs variadic for "exactly one of N")
- Doesn't support dynamic rule lists well
- Forces a single pattern (reduces flexibility)

### Alternative 2: Static Factory Methods

Provide both binary constructors and variadic factories:

```php
class AndBusinessRule extends CompositeBusinessRule {
    // Binary constructor
    public function __construct(
        private BusinessRule $left,
        private BusinessRule $right
    ) {}

    // Variadic factory
    public static function all(BusinessRule ...$rules): self {
        return array_reduce($rules, fn($acc, $r) => $acc->and($r));
    }
}

// Usage
AndBusinessRule::all($a, $b, $c, $d);
```

**Rejected because:**
- More complex API surface
- Still inconsistent (XOR would need variadic constructor anyway)
- Factory methods add cognitive overhead

### Alternative 3: Current Design (Chosen)

Variadic constructors for all composite rules, while promoting fluent API as primary pattern.

**Chosen because:**
- ✅ Consistent across all composite rules
- ✅ Supports both static and dynamic composition
- ✅ Simple mental model
- ✅ Flexible (developers choose the best approach for their use case)

## Usage Guidelines

### When to Use Fluent API (Recommended)

The fluent API is the **primary, recommended approach** for most scenarios:

```php
// ✅ Static composition with known rules
$productRule = $nameValid
    ->and($priceValid)
    ->and($descriptionValid);

// ✅ Readable chaining
$eligibilityRule = $ageValid
    ->and($residencyValid)
    ->or($specialExemption);

// ✅ Better IDE support (covariant return types)
$combined = $rule1->and($rule2); // IDE knows this is AndBusinessRule
```

**Use fluent API when:**
- Rules are known at development time
- You want maximum type safety and IDE autocomplete
- You're building complex compositions with mixed operators
- Code readability is paramount

### When to Use Variadic Constructors

Use variadic constructors for **dynamic scenarios**:

```php
// ✅ Dynamic rule lists
$rules = $this->loadRulesFromConfig('product-validation');
$validator = new AndBusinessRule(...$rules);

// ✅ Multiple options (especially XOR)
$paymentMethod = new XorBusinessRule(
    new PaymentByCreditCard(),
    new PaymentByPayPal(),
    new PaymentByCrypto(),
    new PaymentByBankTransfer()
);

// ✅ Building rules from collections
$fieldRules = array_map(
    fn($field) => $this->createRule($field),
    $requiredFields
);
$allFieldsValid = new AndBusinessRule(...$fieldRules);
```

**Use variadic constructors when:**
- Number of rules is determined at runtime
- Working with collections of rules
- Expressing "one of many" (XOR)
- Loading rules from external sources

## Examples

### Example 1: Product Validation (Static - Use Fluent API)

```php
class Product
{
    public static function create(string $name, float $price, ?string $description): self
    {
        $validator = BusinessRuleValidator::create();

        // Static rules - fluent API is perfect
        $validator
            ->validate(
                (new ProductNameValid())->and(new ProductNameLength()),
                $name,
                'name'
            )
            ->validate(
                (new ProductPriceValid())->and(new ProductPricePositive()),
                $price,
                'price'
            );

        $validator->throwIfInvalid();
        return new self($name, $price, $description);
    }
}
```

### Example 2: Dynamic Validation (Runtime - Use Constructor)

```php
class DynamicValidator
{
    public function validateEntity(string $entityType, array $data): void
    {
        // Load rules from configuration
        $ruleDefinitions = $this->config->get("validation.{$entityType}");

        // Create rule instances dynamically
        $rules = array_map(
            fn($def) => $this->ruleFactory->create($def),
            $ruleDefinitions
        );

        // Combine with variadic constructor
        $combinedRule = new AndBusinessRule(...$rules);

        if (!$combinedRule->isSatisfiedBy($data)) {
            throw new ValidationException("Entity validation failed");
        }
    }
}
```

### Example 3: Payment Method Selection (XOR - Use Constructor)

```php
class Order
{
    public function __construct(
        private array $items,
        private PaymentMethod $paymentMethod
    ) {
        // XOR: Exactly ONE payment method must be selected
        $paymentRule = new XorBusinessRule(
            new PaidByCreditCard(),
            new PaidByPayPal(),
            new PaidByBankTransfer(),
            new PaidByCryptocurrency(),
            new PaidByGiftCard()
        );

        if (!$paymentRule->isSatisfiedBy($paymentMethod)) {
            throw new InvalidArgumentException(
                'Exactly one payment method must be selected'
            );
        }
    }
}
```

### Example 4: Mixed Approach (Best of Both Worlds)

```php
class RegistrationService
{
    public function register(array $userData): User
    {
        // Static rules use fluent API
        $basicValidation = (new EmailValid())
            ->and(new EmailNotInUse())
            ->and(new PasswordStrong());

        // Dynamic rules based on country
        $countryRules = $this->getCountrySpecificRules($userData['country']);
        $countryValidation = new AndBusinessRule(...$countryRules);

        // Combine both
        $fullValidation = $basicValidation->and($countryValidation);

        if (!$fullValidation->isSatisfiedBy($userData)) {
            throw new ValidationException('Registration validation failed');
        }

        return $this->createUser($userData);
    }
}
```

## Implementation Details

### Minimum Required: One Rule

All variadic constructors require at least one rule:

```php
public function __construct(
    BusinessRule $businessRule,        // Required (at least one)
    BusinessRule ...$moreBusinessRules // Optional (variadic)
)
```

This prevents:
```php
new AndBusinessRule(); // ❌ Compile error - good!
```

### Internal Storage

Rules are stored as a flat array:

```php
$this->businessRules = array_merge([$businessRule], $moreBusinessRules);
```

This ensures consistent handling whether constructed with:
- One rule: `new AndBusinessRule($a)`
- Multiple rules: `new AndBusinessRule($a, $b, $c)`

## Consistency with Specification Pattern

The classical Specification Pattern (Evans, Fowler) uses **binary composition**:

```php
interface Specification {
    function isSatisfiedBy($candidate): bool;
    function and(Specification $other): Specification;
    function or(Specification $other): Specification;
    function not(): Specification;
}
```

Our design **extends** this pattern by adding variadic constructors while **preserving** the classical fluent API. This makes the library more flexible without breaking expectations from developers familiar with the pattern.

## Trade-offs

### Advantages

✅ **Consistency** - All composite rules behave identically
✅ **Flexibility** - Two composition patterns for different use cases
✅ **Dynamic support** - Works well with runtime rule lists
✅ **Simplicity** - Flat object graphs, easier debugging
✅ **XOR clarity** - "Exactly one of N" is naturally expressed

### Disadvantages

❌ **Two patterns** - Developers must learn both approaches
❌ **Less opinionated** - No single "right way" to compose
❌ **Binary pattern lost** - Classic specification pattern is binary

The advantages outweigh the disadvantages, particularly given the strong requirement for consistency across composite rule types.

## Conclusion

Variadic constructors for all composite business rules provide a consistent, flexible API that supports both static and dynamic composition scenarios. While the fluent API remains the primary, recommended approach for most use cases, the variadic constructors fill important gaps for runtime composition and "one of many" logic.

This decision prioritizes:
1. **Consistency** - All composite rules work the same way
2. **Flexibility** - Developers can choose the best tool for their use case
3. **Practicality** - Dynamic scenarios are well-supported

The pattern is well-documented, and clear guidelines help developers choose the right approach for their specific needs.

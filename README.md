# DDD Business Rules Specification

A lightweight PHP library implementing the **Specification Pattern** for Domain-Driven Design (DDD). Define, compose, and reuse business rules with a clean, expressive fluent API.

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

## Features

- **Fluent API** - Chain rules with `and()`, `or()`, `not()`, `xor()`
- **Composable** - Build complex rules from simple ones
- **Zero Dependencies** - Pure domain layer, no infrastructure coupling
- **Performance** - Short-circuit evaluation for efficiency

## Installation

```bash
composer require fragoe/ddd-business-rules-specification
```

**Requirements:** PHP ^8.2

## Quick Start

### 1. Define Your Business Rules

```php
use fragoe\DDDBusinessRules\CompositeBusinessRule;

class MinimumAge extends CompositeBusinessRule
{
    public function __construct(private int $minAge) {}

    public function isSatisfiedBy($value): bool
    {
        return $value >= $this->minAge;
    }
}
```

### 2. Compose Rules

```php
$canRegister = (new MinimumAge(18))
    ->and(new HasValidEmail())
    ->or(new IsAdmin());

if ($canRegister->isSatisfiedBy($user)) {
    // User can register
}
```

## Why the Specification Pattern?

The Specification Pattern, introduced by Eric Evans in Domain-Driven Design, is often **underestimated** despite its powerful advantages for complex enterprise applications.

### Single Source of Truth

Business rules are defined **once** in dedicated, testable classes. No more scattered validation logic across controllers, services, and database queries. When a business rule changes, you modify it in exactly one place.

```php
// ✅ One place to define and maintain
class CanApproveOrder extends CompositeBusinessRule { /* ... */ }

// Used everywhere:
if ($rule->isSatisfiedBy($order)) { /* ... */ }           // Runtime validation
$query->where($rule->toExpression());                      // Database queries
$violations = $analyzer->findViolations($rule, $data);     // Static analysis
```

### Analysis & Visualization

Because business rules are expressed as **objects**, not scattered conditionals, you can:

- **Visualize** complex rule hierarchies and dependencies
- **Analyze** which rules are used where in your application
- **Document** business logic automatically from code
- **Test** business rules in isolation

In large enterprise systems with hundreds of business rules, this capability transforms maintainability. The [ddd-business-rules-analyzer](https://github.com/fragoe/ddd-business-rules-analyzer) package demonstrates how static analysis can extract, visualize, and document your entire business rule landscape.

### Enterprise Benefits

- **Audit trails** - Know exactly which rules were evaluated and why
- **Compliance** - Prove business logic matches requirements
- **Refactoring** - Change rules confidently with full test coverage
- **Knowledge transfer** - Business rules are explicit, not hidden in conditionals

## Core Concepts

### BusinessRule Interface

The core contract defining what every business rule must provide:

```php
interface BusinessRule
{
    public function isSatisfiedBy($value): bool;
    public function getCode(): int|string;
    public function getMessage(): string;

    public function and(BusinessRule $other): BusinessRule;
    public function or(BusinessRule $other): BusinessRule;
    public function xor(BusinessRule $other): BusinessRule;
    public function not(): BusinessRule;
}
```

### CompositeBusinessRule

Extend this abstract class to create your own rules. It provides:
- Default implementations for `getCode()` and `getMessage()` (derived from class name)
- Composition methods returning concrete types for IDE support

### Built-in Composites

| Class | Description |
|-------|-------------|
| `AndBusinessRule` | All rules must be satisfied |
| `OrBusinessRule` | At least one rule must be satisfied |
| `XorBusinessRule` | Exactly one rule must be satisfied |
| `NotBusinessRule` | Inverts the rule result |

## Examples

See the [`examples/`](examples/) folder for complete, real-world examples including a Coffee brewing domain with various brew methods (Espresso, French Press, Pour Over, etc.).

## Testing

```bash
./vendor/bin/phpunit
```

## Design Philosophy

This library follows **strict DDD principles** and intentionally does NOT include:
- Database query integration (see [ddd-business-rules-doctrine](https://github.com/fragoe/ddd-business-rules-doctrine))
- Visualization tools (see [ddd-business-rules-analyzer](https://github.com/fragoe/ddd-business-rules-analyzer))
- Serialization or validation framework integration

These concerns are handled by companion packages to keep the core clean and focused.

## Package Ecosystem

- **[fragoe/ddd-business-rules](https://github.com/fragoe/ddd-business-rules)** - Meta package (installs all packages)
- **[fragoe/ddd-business-rules-specification](https://github.com/fragoe/ddd-business-rules-specification)** (this package) - Core Specification Pattern
- **[fragoe/ddd-business-rules-doctrine](https://github.com/fragoe/ddd-business-rules-doctrine)** - Doctrine ORM/DBAL bridge
- **[fragoe/ddd-business-rules-validation](https://github.com/fragoe/ddd-business-rules-validation)** - Validation framework integration
- **[fragoe/ddd-business-rules-analyzer](https://github.com/fragoe/ddd-business-rules-analyzer)** - Static analysis and visualization

## License

MIT License. See [LICENSE](LICENSE) for details.

## Links

- [Specification Pattern](https://en.wikipedia.org/wiki/Specification_pattern)
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)
- [Packagist](https://packagist.org/packages/fragoe/ddd-business-rules-specification)
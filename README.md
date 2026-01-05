# DDD Business Rules Specification

[![Tests](https://github.com/fragoe/ddd-business-rules-specification/actions/workflows/tests.yml/badge.svg)](https://github.com/fragoe/ddd-business-rules-specification/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/fragoe/ddd-business-rules-specification/branch/main/graph/badge.svg)](https://codecov.io/gh/fragoe/ddd-business-rules-specification)
[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

A lightweight PHP library implementing the [Specification Pattern](https://en.wikipedia.org/wiki/Specification_pattern) for Domain-Driven Design. Compose business rules with a fluent API using `and()`, `or()`, `not()`, and `xor()`.

## Installation

```bash
composer require fragoe/ddd-business-rules-specification
```

**Requirements:** PHP ^8.3

## Usage

Extend `CompositeBusinessRule` and implement `isSatisfiedBy()`. Compose rules using the fluent API.

See [`tests/CompositeBusinessRulesTest.php`](tests/CompositeBusinessRulesTest.php) for comprehensive usage examples including rule creation, composition, and fluent chaining. For a real-world domain example, see [`tests/CoffeeExamplesTest.php`](tests/CoffeeExamplesTest.php).

## Built-in Composites

| Class | Description |
|-------|-------------|
| `AndBusinessRule` | All rules must be satisfied |
| `OrBusinessRule` | At least one rule must be satisfied |
| `XorBusinessRule` | Exactly one rule must be satisfied |
| `NotBusinessRule` | Inverts the rule result |

## Design Philosophy

This library follows strict DDD principles and intentionally excludes infrastructure concerns. Database queries, visualization, and validation framework integration are handled by companion packages:

- [ddd-business-rules-doctrine](https://github.com/fragoe/ddd-business-rules-doctrine) - Doctrine bridge
- [ddd-business-rules-analyzer](https://github.com/fragoe/ddd-business-rules-analyzer) - Static analysis and visualization

## Package Ecosystem

| Package | Description |
|---------|-------------|
| [fragoe/ddd-business-rules](https://github.com/fragoe/ddd-business-rules) | Meta package |
| **fragoe/ddd-business-rules-specification** | Core library (this package) |
| [fragoe/ddd-business-rules-doctrine](https://github.com/fragoe/ddd-business-rules-doctrine) | Doctrine bridge |
| [fragoe/ddd-business-rules-validation](https://github.com/fragoe/ddd-business-rules-validation) | Validation integration |
| [fragoe/ddd-business-rules-analyzer](https://github.com/fragoe/ddd-business-rules-analyzer) | Analysis tools |

## License

MIT - See [LICENSE](LICENSE)
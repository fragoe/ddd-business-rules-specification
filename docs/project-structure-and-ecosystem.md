# Project Structure and Ecosystem

## Overview

This document outlines the architectural decisions for the DDD Business Rules project ecosystem, including package structure, naming conventions, and design rationale.

## Project Name

**DDD Business Rules** - A comprehensive ecosystem for implementing business rules using the Specification Pattern in Domain-Driven Design applications.

## Package Ecosystem

### Core Packages

#### 1. `fragoe/ddd-business-rules` (Core Package)
**Status:** ✅ Exists (this repository)

**Purpose:** Pure domain layer implementation of the Specification Pattern

**Contents:**
- `BusinessRule` interface with `isSatisfiedBy($value): bool`
- `CompositeBusinessRule` abstract class with fluent API
- Built-in composites: `AndBusinessRule`, `OrBusinessRule`, `NotBusinessRule`
- XOR support via `xor()` method
- Zero infrastructure dependencies

**Key Decision:** Keep current name, do NOT rename to "specifications"
- Users think in "business rules", not "specifications"
- Current name is intuitive and clear
- "Specification" is an implementation detail

**Optional Enhancement Under Consideration:**
Add `getCode(): string` and `getDescription(): string` to `BusinessRule` interface for better violation reporting.

**Pros:**
- Every rule becomes self-documenting
- Better API error messages
- Easier debugging and logging

**Cons:**
- Slight increase in boilerplate (mitigated by sensible defaults in `CompositeBusinessRule`)
- Adds metadata concern to pure evaluation interface

**Implementation if added:**
```php
abstract class CompositeBusinessRule implements BusinessRule {
    abstract public function isSatisfiedBy($value): bool;

    // Default implementations using class name
    public function getCode(): string {
        return $this->classNameToKebabCase(static::class);
    }

    public function getDescription(): string {
        return $this->classNameToHumanReadable(static::class);
    }
}
```

---

#### 2. `fragoe/ddd-business-rules-validation`
**Status:** 📋 Planned (to be created)

**Purpose:** Violation tracking and reporting for business rules

**Contents:**
- `BusinessRuleViolation` - Single violation with code, message, value
- `BusinessRuleViolationList` / `BusinessRuleViolationListInterface` - Violation collection
- `BusinessRuleViolationException` - Transport violations to upper layers
- `BusinessRuleValidator` - Validates rules and collects violations

**Design Approach:**
- Works with both simple rules and composites
- Recursively collects violations from AND/OR/NOT compositions
- Similar to Symfony's ConstraintViolationList
- Enables clean error propagation to REST/GraphQL APIs

**Usage Example:**
```php
$validator = new BusinessRuleValidator();
$violations = $validator->validate($value, $rule);

if ($violations->hasViolations()) {
    throw new BusinessRuleViolationException($violations);
}

// API response
{
    "errors": [
        {
            "code": "product.description.length",
            "message": "Description must be ≤ 8192 characters",
            "value": "very long text..."
        }
    ]
}
```

**Key Features:**
- Serialization to JSON/Array for API responses
- Property path support for nested validations
- Integration with application/infrastructure layers

---

#### 3. `fragoe/ddd-business-rules-doctrine`
**Status:** ✅ Exists

**Location:** `/Users/frankgoldner/Projects/fragoe/ddd-business-rules-doctrine`

**Purpose:** Doctrine ORM/DBAL bridge for translating domain rules to database queries

**Contents:**
- `DoctrineQueryTranslator` - Converts rules to QueryBuilder
- `TranslatorInterface` - Interface for custom translators
- `RepositoryTrait` - Helper for repository integration
- Automatic translation of AND/OR/NOT composites
- Custom translator registration

**Key Benefit:** Use same rule for in-memory validation AND database queries

**Example:**
```php
$rule = new ProductDescriptionLength();

// In-memory (domain layer)
if ($rule->isSatisfiedBy($value)) { ... }

// Database query (infrastructure layer)
$translator->translate($rule, $qb, 'p');
$products = $qb->getQuery()->getResult();
```

---

#### 4. `fragoe/ddd-business-rules-analyzer`
**Status:** ✅ Exists

**Location:** `/Users/frankgoldner/Projects/fragoe/ddd-business-rules-analyzer`

**Purpose:** Static analysis and visualization tooling

**Contents:**
- PHP Parser integration for AST analysis
- Rule discovery and categorization
- ASCII tree visualization
- CLI tool for analyzing codebases
- Documentation generation capabilities

**Future Enhancements:**
- Complexity metrics
- Rule usage patterns
- GraphViz output
- HTML/JSON export
- **Rule serialization for documentation** (toArray, toJson, toYaml)

**Usage:**
```bash
vendor/bin/analyzer analyze src/
```

---

### Future Packages

#### 5. `fragoe/ddd-business-rules-eloquent`
**Status:** 📋 Planned (future)

**Purpose:** Laravel Eloquent bridge (similar to Doctrine bridge)

**Contents:**
- Query builder translation for Eloquent
- Laravel integration helpers
- Validation rule integration

---

#### 6. `fragoe/ddd-business-rules-debug`
**Status:** ⚠️ Postponed (wait for use cases)

**Potential Purpose:** Debugging and profiling tools

**Possible Contents:**
- Rule execution tracing
- Performance profiling
- Debug logging interceptors
- Rule execution visualization

**Decision:** Don't create yet - wait for specific use cases to emerge

---

#### 7. `fragoe/ddd-business-rules-serializer`
**Status:** ⚠️ Not recommended for standard use

**When to Create:**
Only create if you need **dynamic rule configuration** for:
- SaaS platforms with per-tenant rules
- Rule engines for non-developers
- Admin panels that build rules from UI
- Multi-environment rule configurations

**Why Not Recommended for Standard Use:**
- Business rules should be explicit in code (DDD principle)
- Storing rule definitions in database is often an anti-pattern
- Security risks (code injection if not careful)
- Breaks type safety and IDE support

**Alternative Approach:**
Add serialization features to existing packages:
- **Analyzer Package:** Rule structure export for documentation (toJson, toYaml, toDot)
- **Validation Package:** Violation serialization for API responses

**Exception:** If building a rule engine or dynamic configuration system, then create this package.

---

## Documentation

### `ddd-business-rules` Documentation Site
**Status:** 📋 Planned

**Technology:** MkDocs Material (https://squidfunk.github.io/mkdocs-material/)

**Hosting Options:**
1. GitHub Pages (from `docs/` folder in main repo)
2. ReadTheDocs (separate repo or integration)
3. Dedicated documentation repository

**NOT a Composer Package:**
- Documentation is a website, not installable code
- Generated static HTML, not PHP
- Separate from package distribution

**Contents:**
- Getting started guide
- Core concepts and patterns
- Package-specific documentation
- API reference
- Architecture decisions
- Migration guides
- Examples and recipes

---

## Meta-Package Discussion

### Proposed: `fragoe/ddd-business-rules` (meta-package)
**Status:** ❌ Not Recommended

**Why Not:**
- Forces all dependencies on everyone
- Most users only need: core + validation
- Some need: core + validation + doctrine
- Almost nobody needs: everything

**Better Approach:**
Let users compose what they need:

```bash
# Minimal
composer require fragoe/ddd-business-rules

# With validation
composer require fragoe/ddd-business-rules \
                 fragoe/ddd-business-rules-validation

# With database integration
composer require fragoe/ddd-business-rules \
                 fragoe/ddd-business-rules-validation \
                 fragoe/ddd-business-rules-doctrine

# Development tools
composer require --dev fragoe/ddd-business-rules-analyzer
```

**Alternative (if meta-package desired):**
- Only include: core + validation (essentials)
- List others in `suggest` section of composer.json

---

## Architecture Principles

### 1. Separation of Concerns
Each package has a single, focused responsibility:
- **Core:** Pure rule evaluation
- **Validation:** Violation tracking and reporting
- **Doctrine:** Database query translation
- **Analyzer:** Static analysis and tooling

### 2. Dependency Direction
```
        ┌─────────────────────┐
        │  analyzer (tooling) │
        └──────────┬──────────┘
                   │
        ┌──────────▼──────────┐
        │  validation         │
        └──────────┬──────────┘
                   │
┌──────────────────▼──────────────────┐
│  ddd-business-rules (core)          │
└─────────────────┬───────────────────┘
                  │
        ┌─────────▼─────────┐
        │  doctrine (infra) │
        └───────────────────┘
```

Infrastructure and tooling depend on core, never the reverse.

### 3. Optional Complexity
Users only install what they need:
- Need violations? Add validation package
- Need database queries? Add Doctrine bridge
- Need analysis? Add analyzer as dev dependency

### 4. DDD Compliance
- Domain layer stays pure (core package)
- Infrastructure concerns isolated (Doctrine, future Eloquent)
- Validation is domain-aware but separate
- No domain pollution

### 5. Zero Runtime Overhead
- Core has zero dependencies
- Optional packages add features without slowing down core
- Validation and infrastructure are opt-in

---

## Serialization Strategy

### Use Cases and Solutions

#### 1. Documentation/Visualization
**Need:** Export rule structure for docs, diagrams, analysis

**Solution:** Add to **analyzer package**
```php
$serializer = new RuleSerializer();
$json = $serializer->toJson($rule);
$yaml = $serializer->toYaml($rule);
$dot = $serializer->toDot($rule); // GraphViz
```

#### 2. API Violation Responses
**Need:** Send violations to REST/GraphQL clients

**Solution:** Add to **validation package**
```php
$serializer = new ViolationSerializer();
$json = $serializer->toJson($violations);
$array = $serializer->toArray($violations);
```

#### 3. Dynamic Rule Configuration
**Need:** Store/load rule definitions from database

**Solution:** Create **serializer package** ONLY if:
- Building a SaaS platform with per-tenant rules
- Creating a rule engine for non-developers
- Admin panel needs to build rules from UI
- Multi-environment dynamic configuration

**Default Recommendation:** Keep rules in code (DDD best practice)

---

## Implementation Roadmap

### Phase 1: Core Stabilization
- [x] Core package with BusinessRule interface
- [x] Composite rules (And/Or/Not/XOR)
- [x] Comprehensive tests
- [x] Documentation (README, CHANGELOG)
- [ ] Decide: Add getCode()/getDescription() to interface?

### Phase 2: Validation Package
- [ ] Create repository structure
- [ ] Implement violation classes
- [ ] Create validator with composite support
- [ ] Add serialization for API responses
- [ ] Comprehensive tests
- [ ] Documentation and examples

### Phase 3: Documentation Site
- [ ] Set up MkDocs Material
- [ ] Write comprehensive guides
- [ ] API reference documentation
- [ ] Examples and recipes
- [ ] Deploy to GitHub Pages/ReadTheDocs

### Phase 4: Package Publishing
- [ ] Publish core to Packagist
- [ ] Publish validation to Packagist
- [ ] Publish Doctrine bridge to Packagist
- [ ] Publish analyzer to Packagist
- [ ] Tag v1.0.0 releases

### Phase 5: Ecosystem Expansion
- [ ] Eloquent bridge package
- [ ] Additional integrations as needed
- [ ] Debug package (if use cases emerge)
- [ ] Serializer package (only if needed)

---

## Key Decisions Summary

| Decision | Recommendation | Rationale |
|----------|----------------|-----------|
| Core package name | Keep `ddd-business-rules` | Intuitive, clear purpose |
| Validation package | Create `ddd-business-rules-validation` | Essential for real apps |
| Doctrine bridge | Keep existing package | Already working well |
| Analyzer | Keep existing package | Valuable tooling |
| Debug package | Postpone | Wait for specific use cases |
| Documentation | MkDocs Material site, not package | Proper web docs, not Composer |
| Meta-package | Not recommended | Let users compose needs |
| Serializer package | Only if dynamic rules needed | Most apps keep rules in code |
| getCode()/getDescription() | Under consideration | Improves violations, small cost |

---

## Package Versions

All packages will use semantic versioning:
- Core: v1.0.0
- Validation: v1.0.0 (synced with core)
- Doctrine: v1.0.0 (synced with core)
- Analyzer: v1.0.0 (independent versioning acceptable)

---

## Conclusion

This ecosystem provides a comprehensive, modular approach to business rules in DDD applications:

1. **Start simple:** Just core package for basic needs
2. **Add validation:** When you need violation reporting
3. **Add persistence:** Doctrine/Eloquent for database integration
4. **Add tooling:** Analyzer for development assistance

Each piece is optional, focused, and composable - following the Unix philosophy of "do one thing well."

The structure balances:
- ✅ DDD purity in the core
- ✅ Practical features in extensions
- ✅ Clean separation of concerns
- ✅ Pay-for-what-you-use philosophy

Next step: Create the validation package to enable complete violation tracking and reporting.

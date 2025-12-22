# Database Query Integration

## The Question

> The author of https://github.com/tanigami/specification-php added `whereExpression` methods to make the rule/specification be reusable within a repository. First of all, this looks like breaking the rules of DDD - domain logic, like the business rules, are located in the domain layer, which shouldn't know anything about the infrastructure layer. Unfortunately, using this in combination with Doctrine DBAL could be very powerful. What are your thoughts on this? Would it make sense to create another Git repository, maybe "ddd-business-rules-doctrine", which extends this one? So, the user has the choice to use the simple, clean version or the "slightly polluted" one.

## The Problem: DDD Layer Violation

Adding `whereExpression()` methods directly to domain business rules violates DDD's layered architecture:

```php
// ❌ This violates DDD
interface BusinessRule {
    public function isSatisfiedBy($value): bool;
    public function whereExpression(): string;  // Domain knows about SQL!
}
```

**Problems:**
- The domain layer becomes coupled to SQL syntax
- Domain rules are no longer infrastructure-agnostic
- Cannot easily swap out persistence mechanisms
- Violates separation of concerns

## The Solution: Separate Repository with Translator Pattern

**Yes, create a separate repository**, but use the **Translator Pattern** instead of inheritance.

### ❌ Don't Do This (Inheritance Approach)
```php
// In ddd-business-rules-doctrine package
class DoctrineBusinessRule extends BusinessRule {
    public function whereExpression(): string { ... }
}
```

**Why this is bad:**
- Users must write rules twice (domain version + doctrine version)
- Or they use only the Doctrine version (polluting domain layer)
- Tight coupling between packages

### ✅ Do This Instead (Translator Pattern)

Create a **separate translator** that converts pure domain rules into database queries:

```php
// In ddd-business-rules-doctrine package
interface QueryTranslator {
    public function translate(BusinessRule $rule, QueryBuilder $qb, string $alias): QueryBuilder;
}

class DoctrineQueryTranslator implements QueryTranslator {
    public function translate(BusinessRule $rule, QueryBuilder $qb, string $alias): QueryBuilder {
        // Use visitor pattern or reflection to analyze the rule
        // and build Doctrine query
    }
}
```

## Recommended Architecture

### Repository 1: `ddd-business-rules` (Current - Pure Domain)

**This repository** - Remains infrastructure-agnostic:

```php
// Domain layer - no infrastructure knowledge
interface BusinessRule {
    public function isSatisfiedBy($value): bool;
}

class ProductDescriptionLength extends CompositeBusinessRule {
    public function isSatisfiedBy($value): bool {
        return $value === null || (is_string($value) && mb_strlen($value) <= 8192);
    }
}
```

### Repository 2: `ddd-business-rules-doctrine` (Infrastructure Bridge)

**Future repository** - Bridges domain to database:

```php
// Infrastructure layer - translates domain rules to queries
interface QueryableBusinessRule {
    public function toQueryBuilder(QueryBuilder $qb, string $alias): QueryBuilder;
}

class ProductDescriptionLengthTranslator implements QueryableBusinessRule {
    public function translate(
        ProductDescriptionLength $rule,
        QueryBuilder $qb,
        string $alias
    ): QueryBuilder {
        return $qb
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull("$alias.description"),
                $qb->expr()->andX(
                    $qb->expr()->isNotNull("$alias.description"),
                    $qb->expr()->lte("LENGTH($alias.description)", 8192)
                )
            ));
    }
}
```

## Automatic Translation

The Doctrine bridge package could use **reflection, visitor pattern, or registries** to automatically translate composite rules:

```php
class AutomaticQueryTranslator {
    private array $customTranslators = [];

    public function register(string $ruleClass, callable $translator): void {
        $this->customTranslators[$ruleClass] = $translator;
    }

    public function translate(BusinessRule $rule, QueryBuilder $qb, string $alias): QueryBuilder {
        if ($rule instanceof AndBusinessRule) {
            // Use visitor to traverse children and combine with andWhere()
            foreach ($rule->getChildren() as $child) {
                $this->translate($child, $qb, $alias);
            }
        } elseif ($rule instanceof OrBusinessRule) {
            // Combine with orWhere()
            $expressions = [];
            foreach ($rule->getChildren() as $child) {
                $subQb = clone $qb;
                $this->translate($child, $subQb, $alias);
                $expressions[] = $subQb->getDQLPart('where');
            }
            $qb->andWhere($qb->expr()->orX(...$expressions));
        } elseif ($rule instanceof NotBusinessRule) {
            // Use NOT expression
        } else {
            // For custom rules, use registered translators
            if (!isset($this->customTranslators[$rule::class])) {
                throw new \RuntimeException("No translator registered for " . $rule::class);
            }
            return $this->customTranslators[$rule::class]($rule, $qb, $alias);
        }

        return $qb;
    }
}
```

## Proposed Package Structure

### `ddd-business-rules-doctrine/`
```
src/
├─ Translator/
│  ├─ QueryTranslatorInterface.php    # Main translator contract
│  ├─ DoctrineQueryTranslator.php     # Doctrine implementation
│  └─ TranslatorRegistry.php          # Register custom translators
├─ Visitor/
│  └─ QueryBuildingVisitor.php        # Visitor pattern implementation
├─ Bridge/
│  ├─ RepositoryTrait.php             # Helper trait for repositories
│  └─ QueryableSpecification.php      # Optional wrapper interface
└─ Exception/
   └─ TranslationException.php         # When rule can't be translated
```

## Usage Example

```php
// In your repository (infrastructure layer)
class ProductRepository {
    public function __construct(
        private EntityManager $em,
        private QueryTranslator $translator
    ) {}

    public function findByRule(BusinessRule $rule): array {
        $qb = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p');

        $this->translator->translate($rule, $qb, 'p');

        return $qb->getQuery()->getResult();
    }
}

// Application setup
$translator = new DoctrineQueryTranslator();
$translator->register(
    ProductDescriptionLength::class,
    new ProductDescriptionLengthTranslator()
);

$repository = new ProductRepository($entityManager, $translator);

// Usage - same rule works for both!
$rule = new ProductDescriptionLength();

// In-memory validation (domain layer)
if ($rule->isSatisfiedBy($product->getDescription())) {
    // Valid!
}

// Database query (infrastructure layer)
$products = $repository->findByRule($rule);
```

## Benefits of This Approach

✅ **Pure domain layer** - No SQL or database knowledge in business rules
✅ **Single source of truth** - One rule class, usable in two contexts
✅ **Flexible** - Can add translators for other ORMs (Eloquent, Cycle, etc.)
✅ **Optional** - Users can choose pure domain or add persistence bridge
✅ **Testable** - Domain rules test business logic, translators test SQL generation separately
✅ **DDD compliant** - Clean separation of concerns across layers
✅ **Maintainable** - Each package has a single, clear responsibility

## Recommended Package Ecosystem

1. **`fragoe/ddd-business-rules`** (Current repository)
   - Pure domain layer
   - No infrastructure dependencies
   - Focus: Business rule evaluation

2. **`fragoe/ddd-business-rules-doctrine`** (Future repository)
   - Infrastructure bridge for Doctrine ORM/DBAL
   - Translates rules to QueryBuilder/DQL
   - Depends on: `fragoe/ddd-business-rules` + `doctrine/orm`

3. **`fragoe/ddd-business-rules-eloquent`** (Optional future)
   - Infrastructure bridge for Laravel Eloquent
   - Translates rules to Eloquent query builder
   - Depends on: `fragoe/ddd-business-rules` + `illuminate/database`

4. **`fragoe/ddd-business-rules-analyzer`** (Optional future)
   - Static analysis and visualization tooling
   - CLI for debugging and documentation
   - Depends on: `nikic/php-parser`

## When to Use What

**Use only `ddd-business-rules` when:**
- Building pure domain models
- In-memory validation is sufficient
- Working with small datasets
- DDD purity is critical

**Add `ddd-business-rules-doctrine` when:**
- Need to filter database queries by rules
- Working with large datasets
- Want to avoid N+1 query problems
- Need to push business logic to the database layer

## Design Principles

This separation maintains these key DDD principles:

1. **Domain Ignorance** - Domain layer knows nothing about persistence
2. **Infrastructure Flexibility** - Can swap Doctrine for another ORM
3. **Single Responsibility** - Each package does one thing well
4. **Dependency Direction** - Infrastructure depends on domain, never reverse
5. **Optional Complexity** - Users pay cost only for what they use

## Implementation Notes

When creating the `ddd-business-rules-doctrine` package:

- Consider using the **Visitor Pattern** for traversing composite rules
- Provide a **TranslatorRegistry** for registering custom rule translators
- Support both **automatic translation** (for simple rules) and **manual registration** (for complex rules)
- Throw clear exceptions when a rule cannot be automatically translated
- Include comprehensive documentation with examples for common use cases
- Consider supporting both Doctrine ORM (entities) and DBAL (raw queries)

## Conclusion

Keep this repository pure and create separate infrastructure bridges. This approach:
- Respects DDD architecture
- Gives users choice (pure vs. persistence-aware)
- Maintains flexibility for future persistence mechanisms
- Keeps each package focused and maintainable

The `whereExpression()` approach is powerful but architecturally problematic. The translator pattern provides the same power without the architectural compromise.

# Package Ecosystem

This document provides an overview of the DDD Business Rules package ecosystem.

> **Note:** For detailed architectural decisions and rationale, see [Project Structure and Ecosystem](./project-structure-and-ecosystem.md).

## Core Package

### `fragoe/ddd-business-rules` (This Repository)

**Purpose:** Pure domain layer implementation of the Specification Pattern for business rules.

**Location:** `/Users/frankgoldner/Projects/fragoe/ddd-business-rules`

**Features:**
- `BusinessRule` interface with `isSatisfiedBy()` method
- `CompositeBusinessRule` abstract class with fluent API
- Built-in composites: `AndBusinessRule`, `OrBusinessRule`, `NotBusinessRule`
- Supports AND, OR, NOT, XOR operations
- Zero dependencies (except dev dependencies)
- Infrastructure-agnostic

**Installation:**
```bash
composer require fragoe/ddd-business-rules
```

## Companion Packages

### `fragoe/ddd-business-rules-analyzer`

**Purpose:** Static analysis and visualization tooling for business rules.

**Location:** `/Users/frankgoldner/Projects/fragoe/ddd-business-rules-analyzer`

**Git Status:** ✅ Initialized (main branch, clean working tree)

**Features:**
- Parser for discovering BusinessRule classes using nikic/php-parser
- ASCII tree visualization
- CLI tool for analyzing codebases
- Rule categorization (built-in, composite, custom)
- No modifications required to domain code

**Installation:**
```bash
composer require --dev fragoe/ddd-business-rules-analyzer
```

**Usage:**
```bash
vendor/bin/analyzer analyze src/
```

**Key Files:**
- `src/Parser/RuleParser.php` - Discovers rules using AST parsing
- `src/Analyzer/RuleAnalyzer.php` - Main analyzer class
- `src/Visualizer/TreeRenderer.php` - ASCII tree output
- `src/Command/AnalyzeCommand.php` - CLI command
- `bin/analyzer` - Executable entry point

### `fragoe/ddd-business-rules-doctrine`

**Purpose:** Doctrine ORM/DBAL bridge for translating domain rules to database queries.

**Location:** `/Users/frankgoldner/Projects/fragoe/ddd-business-rules-doctrine`

**Git Status:** ✅ Initialized (main branch, clean working tree)

**Features:**
- Translator pattern for converting rules to QueryBuilder
- Automatic translation of composite rules (AND, OR, NOT)
- Custom translator registration for domain-specific rules
- Repository trait for easy integration
- Maintains DDD purity (no domain pollution)

**Installation:**
```bash
composer require fragoe/ddd-business-rules-doctrine
```

**Usage:**
```php
$translator = new DoctrineQueryTranslator();
$translator->register(new MyCustomRuleTranslator());

$qb = $entityManager->createQueryBuilder()
    ->select('p')
    ->from(Product::class, 'p');

$translator->translate($rule, $qb, 'p');
```

**Key Files:**
- `src/Translator/DoctrineQueryTranslator.php` - Main translator
- `src/Translator/TranslatorInterface.php` - Interface for custom translators
- `src/Bridge/RepositoryTrait.php` - Helper trait for repositories
- `src/Exception/TranslationException.php` - Exception handling
- `examples/` - Complete usage examples

## Architecture Principles

### Separation of Concerns

Each package has a single, focused responsibility:

1. **Core Library** - Pure domain logic, no infrastructure knowledge
2. **Analyzer** - Development tooling, static analysis
3. **Doctrine Bridge** - Infrastructure layer, query translation

### Dependency Direction

```
┌─────────────────────────────────────────────────┐
│  ddd-business-rules-analyzer                    │
│  (depends on: nikic/php-parser, symfony/console)│
│                     │                            │
│                     ├──> ddd-business-rules      │
│                     │    (no dependencies)       │
│  ddd-business-rules-doctrine                    │
│  (depends on: doctrine/orm)                     │
└─────────────────────────────────────────────────┘
```

Infrastructure packages depend on the core, never the reverse.

### Design Philosophy

**Why Separate Packages?**

✅ **Core stays pure** - No visualization or database code in domain
✅ **Zero runtime overhead** - Only install what you need
✅ **Independent evolution** - Each package versions independently
✅ **DDD compliant** - Proper layer separation
✅ **Optional complexity** - Users choose their level of integration

See documentation:
- [Static Analysis Approach](./static-analysis-approach.md)
- [Database Query Integration](./database-query-integration.md)

## Package Status

| Package | Status | Git | Initial Commit |
|---------|--------|-----|----------------|
| `ddd-business-rules` | ✅ Active | ✅ Initialized | ✅ Yes |
| `ddd-business-rules-analyzer` | ✅ Created | ✅ Initialized | ✅ Yes |
| `ddd-business-rules-doctrine` | ✅ Created | ✅ Initialized | ✅ Yes |

## Next Steps

### For the Core Package (`ddd-business-rules`)

- [ ] Publish to Packagist
- [ ] Add more comprehensive tests
- [ ] Create GitHub repository
- [ ] Set up CI/CD pipeline
- [ ] Tag v1.0.0 release

### For the Analyzer Package

- [ ] Add comprehensive tests
- [ ] Implement additional commands (visualize, docs, export)
- [ ] Add complexity metrics
- [ ] Support multiple output formats (JSON, HTML)
- [ ] Publish to Packagist

### For the Doctrine Package

- [ ] Add comprehensive tests with in-memory SQLite
- [ ] Add support for more complex translations
- [ ] Document common translation patterns
- [ ] Add validator to ensure rules match queries
- [ ] Publish to Packagist

### Future Packages

- **`ddd-business-rules-eloquent`** - Laravel Eloquent bridge
- **`ddd-business-rules-mongodb`** - MongoDB query translation
- **`ddd-business-rules-validation`** - Integration with Symfony Validator

## Local Development

All packages are currently in local development mode in:
```
/Users/frankgoldner/Projects/fragoe/
├── ddd-business-rules/           (core)
├── ddd-business-rules-analyzer/  (tooling)
└── ddd-business-rules-doctrine/  (infrastructure)
```

To use them together locally during development, use Composer's path repository:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../ddd-business-rules"
        },
        {
            "type": "path",
            "url": "../ddd-business-rules-analyzer"
        }
    ]
}
```

## License

All packages are released under the MIT License.

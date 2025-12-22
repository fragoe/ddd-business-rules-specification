# Static Analysis Approach for Visualization and Debugging

## The Question

> Would it be possible to leave the code as it is, clean, simple, and expressive, and using static code analysis for the visualization/debugging tool which could be in a separate repository?

## The Answer

**Yes, absolutely!** This is the cleanest approach and aligns perfectly with the Single Responsibility Principle.

## Why Static Analysis is the Right Choice

The business rules library should focus on **one thing**: evaluating business rules. Visualization and debugging are separate concerns that belong in tooling, not in the core library.

### Advantages

✅ **Library stays pure** - No visualization code polluting business logic
✅ **Tooling evolves independently** - Update analyzer without touching library
✅ **Zero runtime overhead** - No visitor pattern or serialization code
✅ **Better separation** - Development tools separate from production code
✅ **Multiple output formats** - JSON, ASCII tree, HTML, graphs, etc.

### What Static Analysis Can Provide

A separate CLI tool can:

**1. Visualize Custom Rule Implementations**
```php
// Your code
class ProductDescriptionLength extends CompositeBusinessRule {
    public function isSatisfiedBy($value): bool { ... }
}

// CLI tool shows:
ProductDescriptionLength
├─ checks: string length ≤ 8192
├─ accepts: null
└─ rejects: non-strings
```

**2. Trace Composition Patterns**
```php
// Your code
$valid = (new MinLength(10))
    ->and(new MaxLength(100))
    ->and(new NoSpecialChars());

// CLI tool parses and shows:
AND
├─ MinLength(10)
├─ MaxLength(100)
└─ NoSpecialChars
```

**3. Analyze Rule Usage Across Codebase**
- Where rules are defined
- How often they're used
- Common composition patterns
- Complexity metrics

**4. Generate Documentation**
- Auto-generate rule catalogs
- Show rule relationships
- Create dependency graphs

## Implementation Approach

### Recommended Tool Stack

- **nikic/php-parser** - Parse PHP AST (Abstract Syntax Tree)
- **symfony/console** - CLI interface
- **phpstan/phpdoc-parser** - Extract docblock information

### Example Tool Structure

```
ddd-business-rules-analyzer/
├─ src/
│  ├─ Parser/RuleParser.php           # Finds BusinessRule classes
│  ├─ Analyzer/CompositionAnalyzer.php # Traces and(), or(), not()
│  ├─ Visualizer/TreeRenderer.php      # Generates tree output
│  └─ Command/AnalyzeCommand.php       # CLI entry point
├─ composer.json
└─ README.md
```

### Basic Usage (Planned)

```bash
# Analyze all rules in a project
php analyzer.phar analyze src/

# Visualize a specific rule class
php analyzer.phar visualize ProductDescriptionLength

# Generate rule documentation
php analyzer.phar docs --output=docs/rules.md

# Show composition patterns
php analyzer.phar patterns
```

## Limitation: Dynamic Composition

Static analysis **cannot** visualize rules composed dynamically at runtime:

```php
// This CANNOT be statically analyzed
function buildRule($config) {
    $rule = new BaseRule();
    foreach ($config['conditions'] as $condition) {
        $rule = $rule->and(new $condition['class'](...$condition['args']));
    }
    return $rule;
}
```

**Solution:** For runtime visualization of dynamically composed rules, you would need to add runtime introspection to the library (visitor pattern, `toTree()` method, or `specifications()` getter). However, most use cases won't need this.

## When to Reconsider

Add runtime visualization to the library only if you discover users need to:
- Debug complex runtime-composed rules
- Serialize rule state to database/JSON
- Generate human-readable explanations from rule instances
- Build dynamic rule builders with live preview

Until then, keep the library clean and use static analysis for tooling.

## Proof of Concept

See `tools/analyzer.php` for a basic proof-of-concept that demonstrates:
- Finding BusinessRule classes
- Detecting composition patterns
- Displaying rule hierarchy

This can be expanded into a full-featured analyzer in a separate repository.

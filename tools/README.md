# Business Rules Analyzer - Proof of Concept

This directory contains a proof-of-concept static analyzer that demonstrates how to build tooling for the business rules library without modifying the library itself.

## Usage

```bash
# Analyze the src directory
php tools/analyzer.php src/

# Analyze the tests directory
php tools/analyzer.php tests/

# Analyze both
php tools/analyzer.php
```

## What It Does

The analyzer currently:

- 🔍 **Discovers all BusinessRule implementations** in a directory
- 📦 **Categorizes rules** (built-in, composite, custom)
- 🔗 **Finds composition patterns** (usage of `->and()`, `->or()`, `->not()`, `->xor()`)
- 📊 **Generates a summary report**

## Example Output

```
═══════════════════════════════════════════════════════════════
  Business Rules Analyzer - Proof of Concept
═══════════════════════════════════════════════════════════════

📋 DISCOVERED BUSINESS RULES
───────────────────────────────────────────────────────────────

Built-in Rules:
  • AndBusinessRule
    └─ src/AndBusinessRule.php
  • OrBusinessRule
    └─ src/OrBusinessRule.php
  • NotBusinessRule
    └─ src/NotBusinessRule.php

Custom Rules:
  • ProductDescriptionLength
    └─ tests/BusinessRuleTest.php

📊 SUMMARY
───────────────────────────────────────────────────────────────
  Total Rules Found: 4
  Composition Patterns: 0
```

## Limitations

This is a **proof of concept** using basic regex and file parsing. A production-ready analyzer would use:

- **nikic/php-parser** for robust AST parsing
- **Proper method call chain tracing** to visualize complex compositions
- **Visual tree generation** (ASCII art, graphviz, etc.)
- **Docblock extraction** for rule descriptions
- **Complexity metrics** (cyclomatic complexity, nesting depth)
- **Multiple output formats** (JSON, HTML, Markdown, SVG)

## Future Full Implementation

A full-featured analyzer could be developed as a separate repository:

```
ddd-business-rules-analyzer/
├─ src/
│  ├─ Parser/
│  │  ├─ RuleParser.php          # Parse BusinessRule classes
│  │  └─ CompositionParser.php   # Parse method chains
│  ├─ Analyzer/
│  │  ├─ ComplexityAnalyzer.php  # Measure rule complexity
│  │  └─ UsageAnalyzer.php       # Find rule usage patterns
│  ├─ Visualizer/
│  │  ├─ TreeRenderer.php        # ASCII tree output
│  │  ├─ GraphvizRenderer.php    # DOT graph generation
│  │  └─ HtmlRenderer.php        # Interactive HTML
│  └─ Command/
│     ├─ AnalyzeCommand.php      # php analyzer analyze src/
│     ├─ VisualizeCommand.php    # php analyzer visualize MyRule
│     └─ DocsCommand.php         # php analyzer docs --output=docs/
└─ composer.json
```

### Planned Commands

```bash
# Analyze all rules in a project
php analyzer.phar analyze src/ --format=tree

# Visualize a specific rule class
php analyzer.phar visualize ProductDescriptionLength

# Generate rule documentation
php analyzer.phar docs --output=docs/rules.md

# Show composition patterns
php analyzer.phar patterns --min-depth=3

# Export to JSON
php analyzer.phar export --format=json > rules.json
```

## Why This Approach?

By keeping analysis tooling **separate from the library**:

✅ Library stays clean and focused
✅ No runtime overhead from visualization code
✅ Tools can evolve independently
✅ Multiple tools can be built (CLI, web UI, IDE plugins)
✅ Users not interested in tooling don't carry the dependency weight

See `docs/static-analysis-approach.md` for the full rationale.

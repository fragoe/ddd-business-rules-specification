#!/usr/bin/env php
<?php

/**
 * Proof-of-Concept Business Rule Analyzer
 *
 * This simple analyzer demonstrates static analysis of BusinessRule classes
 * without requiring any modifications to the library code.
 *
 * A full implementation would use nikic/php-parser for robust AST parsing.
 * This proof-of-concept uses basic regex and reflection for simplicity.
 *
 * Usage:
 *   php tools/analyzer.php [directory]
 */

require_once __DIR__ . '/../vendor/autoload.php';

class BusinessRuleAnalyzer
{
    private array $rules = [];
    private array $compositions = [];

    public function analyze(string $directory): void
    {
        $this->findRules($directory);
        $this->analyzeCompositions($directory);
    }

    private function findRules(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            // Find class definitions that implement BusinessRule or extend CompositeBusinessRule
            if (preg_match('/class\s+(\w+)\s+(?:extends\s+CompositeBusinessRule|implements\s+BusinessRule)/i', $content, $matches)) {
                $className = $matches[1];

                // Determine type
                $type = 'custom';
                if (strpos($content, 'extends CompositeBusinessRule') !== false) {
                    $type = 'composite';
                }
                if (in_array($className, ['AndBusinessRule', 'OrBusinessRule', 'NotBusinessRule'])) {
                    $type = 'built-in';
                }

                $this->rules[] = [
                    'name' => $className,
                    'file' => $file->getPathname(),
                    'type' => $type,
                ];
            }
        }
    }

    private function analyzeCompositions(string $directory): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);

            foreach ($lines as $lineNum => $line) {
                // Find composition patterns: ->and(), ->or(), ->not(), ->xor()
                if (preg_match('/->(?:and|or|not|xor)\s*\(/', $line)) {
                    $this->compositions[] = [
                        'file' => $file->getPathname(),
                        'line' => $lineNum + 1,
                        'code' => trim($line),
                    ];
                }
            }
        }
    }

    public function displayReport(): void
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "  Business Rules Analyzer - Proof of Concept\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "\n";

        $this->displayRulesSection();
        $this->displayCompositionsSection();
        $this->displaySummary();
    }

    private function displayRulesSection(): void
    {
        echo "📋 DISCOVERED BUSINESS RULES\n";
        echo "───────────────────────────────────────────────────────────────\n";

        $grouped = [];
        foreach ($this->rules as $rule) {
            $grouped[$rule['type']][] = $rule;
        }

        foreach (['built-in', 'composite', 'custom'] as $type) {
            if (!isset($grouped[$type])) {
                continue;
            }

            $label = ucfirst($type) . ' Rules';
            echo "\n{$label}:\n";

            foreach ($grouped[$type] as $rule) {
                $relativePath = str_replace(getcwd() . '/', '', $rule['file']);
                echo "  • {$rule['name']}\n";
                echo "    └─ {$relativePath}\n";
            }
        }
        echo "\n";
    }

    private function displayCompositionsSection(): void
    {
        echo "🔗 COMPOSITION PATTERNS\n";
        echo "───────────────────────────────────────────────────────────────\n";

        if (empty($this->compositions)) {
            echo "  No composition patterns found in analyzed files.\n\n";
            return;
        }

        foreach ($this->compositions as $comp) {
            $relativePath = str_replace(getcwd() . '/', '', $comp['file']);
            echo "  {$relativePath}:{$comp['line']}\n";
            echo "  └─ {$comp['code']}\n\n";
        }
    }

    private function displaySummary(): void
    {
        echo "📊 SUMMARY\n";
        echo "───────────────────────────────────────────────────────────────\n";
        echo "  Total Rules Found: " . count($this->rules) . "\n";
        echo "  Composition Patterns: " . count($this->compositions) . "\n";
        echo "\n";
        echo "💡 This is a proof-of-concept. A full analyzer would:\n";
        echo "   • Use nikic/php-parser for robust AST parsing\n";
        echo "   • Trace method call chains (->and()->or()->not())\n";
        echo "   • Generate visual tree diagrams\n";
        echo "   • Extract docblock descriptions\n";
        echo "   • Analyze rule complexity metrics\n";
        echo "   • Export to JSON/HTML/Markdown\n";
        echo "\n";
    }
}

// Main execution
$directory = $argv[1] ?? __DIR__ . '/../src';

if (!is_dir($directory)) {
    echo "Error: Directory '{$directory}' not found.\n";
    echo "Usage: php tools/analyzer.php [directory]\n";
    exit(1);
}

echo "Analyzing directory: {$directory}\n";

$analyzer = new BusinessRuleAnalyzer();
$analyzer->analyze($directory);
$analyzer->displayReport();

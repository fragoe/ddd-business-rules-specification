<?php

namespace fragoe\DDDBusinessRules\Tests;

use fragoe\DDDBusinessRules\AndBusinessRule;
use fragoe\DDDBusinessRules\CompositeBusinessRule;
use fragoe\DDDBusinessRules\NotBusinessRule;
use fragoe\DDDBusinessRules\OrBusinessRule;
use fragoe\DDDBusinessRules\XorBusinessRule;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the core business rule composition functionality.
 */
class CompositeBusinessRulesTest extends TestCase
{
    // Test helper rules
    private function alwaysTrue(): CompositeBusinessRule
    {
        return new class extends CompositeBusinessRule {
            public function isSatisfiedBy($value): bool
            {
                return true;
            }
        };
    }

    private function alwaysFalse(): CompositeBusinessRule
    {
        return new class extends CompositeBusinessRule {
            public function isSatisfiedBy($value): bool
            {
                return false;
            }
        };
    }

    private function isPositive(): CompositeBusinessRule
    {
        return new class extends CompositeBusinessRule {
            protected const CODE = 'test.positive';
            protected const MESSAGE = 'Value must be positive';

            public function isSatisfiedBy($value): bool
            {
                return is_numeric($value) && $value > 0;
            }
        };
    }

    private function isEven(): CompositeBusinessRule
    {
        return new class extends CompositeBusinessRule {
            protected const CODE = 'test.even';
            protected const MESSAGE = 'Value must be even';

            public function isSatisfiedBy($value): bool
            {
                return is_int($value) && $value % 2 === 0;
            }
        };
    }

    // AndBusinessRule Tests

    public function testAndBusinessRuleBothTrue(): void
    {
        $rule = new AndBusinessRule($this->alwaysTrue(), $this->alwaysTrue());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testAndBusinessRuleFirstFalse(): void
    {
        $rule = new AndBusinessRule($this->alwaysFalse(), $this->alwaysTrue());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testAndBusinessRuleSecondFalse(): void
    {
        $rule = new AndBusinessRule($this->alwaysTrue(), $this->alwaysFalse());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testAndBusinessRuleBothFalse(): void
    {
        $rule = new AndBusinessRule($this->alwaysFalse(), $this->alwaysFalse());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testAndBusinessRuleVariadic(): void
    {
        $rule = new AndBusinessRule(
            $this->alwaysTrue(),
            $this->alwaysTrue(),
            $this->alwaysTrue()
        );
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testAndBusinessRuleVariadicOneFalse(): void
    {
        $rule = new AndBusinessRule(
            $this->alwaysTrue(),
            $this->alwaysFalse(),
            $this->alwaysTrue()
        );
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testAndBusinessRuleGetCode(): void
    {
        $rule = new AndBusinessRule($this->isPositive(), $this->isEven());
        $this->assertEquals('(test.positive AND test.even)', $rule->getCode());
    }

    public function testAndBusinessRuleGetMessage(): void
    {
        $rule = new AndBusinessRule($this->isPositive(), $this->isEven());
        $this->assertEquals('All of the following must be satisfied: Value must be positive, Value must be even', $rule->getMessage());
    }

    // OrBusinessRule Tests

    public function testOrBusinessRuleBothTrue(): void
    {
        $rule = new OrBusinessRule($this->alwaysTrue(), $this->alwaysTrue());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testOrBusinessRuleFirstTrue(): void
    {
        $rule = new OrBusinessRule($this->alwaysTrue(), $this->alwaysFalse());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testOrBusinessRuleSecondTrue(): void
    {
        $rule = new OrBusinessRule($this->alwaysFalse(), $this->alwaysTrue());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testOrBusinessRuleBothFalse(): void
    {
        $rule = new OrBusinessRule($this->alwaysFalse(), $this->alwaysFalse());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testOrBusinessRuleVariadic(): void
    {
        $rule = new OrBusinessRule(
            $this->alwaysFalse(),
            $this->alwaysFalse(),
            $this->alwaysTrue()
        );
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testOrBusinessRuleGetCode(): void
    {
        $rule = new OrBusinessRule($this->isPositive(), $this->isEven());
        $this->assertEquals('(test.positive OR test.even)', $rule->getCode());
    }

    public function testOrBusinessRuleGetMessage(): void
    {
        $rule = new OrBusinessRule($this->isPositive(), $this->isEven());
        $this->assertEquals('At least one of the following must be satisfied: Value must be positive, Value must be even', $rule->getMessage());
    }

    // NotBusinessRule Tests

    public function testNotBusinessRuleTrue(): void
    {
        $rule = new NotBusinessRule($this->alwaysFalse());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testNotBusinessRuleFalse(): void
    {
        $rule = new NotBusinessRule($this->alwaysTrue());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testNotBusinessRuleGetCode(): void
    {
        $rule = new NotBusinessRule($this->isPositive());
        $this->assertEquals('NOT(test.positive)', $rule->getCode());
    }

    public function testNotBusinessRuleGetMessage(): void
    {
        $rule = new NotBusinessRule($this->isPositive());
        $this->assertEquals('Must NOT satisfy: Value must be positive', $rule->getMessage());
    }

    // XorBusinessRule Tests

    public function testXorBusinessRuleFirstTrue(): void
    {
        $rule = new XorBusinessRule($this->alwaysTrue(), $this->alwaysFalse());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testXorBusinessRuleSecondTrue(): void
    {
        $rule = new XorBusinessRule($this->alwaysFalse(), $this->alwaysTrue());
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testXorBusinessRuleBothTrue(): void
    {
        $rule = new XorBusinessRule($this->alwaysTrue(), $this->alwaysTrue());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testXorBusinessRuleBothFalse(): void
    {
        $rule = new XorBusinessRule($this->alwaysFalse(), $this->alwaysFalse());
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testXorBusinessRuleVariadicExactlyOne(): void
    {
        $rule = new XorBusinessRule(
            $this->alwaysFalse(),
            $this->alwaysTrue(),
            $this->alwaysFalse()
        );
        $this->assertTrue($rule->isSatisfiedBy(null));
    }

    public function testXorBusinessRuleVariadicTwoTrue(): void
    {
        $rule = new XorBusinessRule(
            $this->alwaysTrue(),
            $this->alwaysTrue(),
            $this->alwaysFalse()
        );
        $this->assertFalse($rule->isSatisfiedBy(null));
    }

    public function testXorBusinessRuleGetCode(): void
    {
        $rule = new XorBusinessRule($this->isPositive(), $this->isEven());
        $this->assertEquals('(test.positive XOR test.even)', $rule->getCode());
    }

    public function testXorBusinessRuleGetMessage(): void
    {
        $rule = new XorBusinessRule($this->isPositive(), $this->isEven());
        $this->assertEquals('Only one of the following must be satisfied: Value must be positive, Value must be even', $rule->getMessage());
    }

    // Fluent API Tests

    public function testFluentAndMethod(): void
    {
        $rule = $this->isPositive()->and($this->isEven());
        $this->assertInstanceOf(AndBusinessRule::class, $rule);
        $this->assertTrue($rule->isSatisfiedBy(4));
        $this->assertFalse($rule->isSatisfiedBy(3));
        $this->assertFalse($rule->isSatisfiedBy(-4));
    }

    public function testFluentOrMethod(): void
    {
        $rule = $this->isPositive()->or($this->isEven());
        $this->assertInstanceOf(OrBusinessRule::class, $rule);
        $this->assertTrue($rule->isSatisfiedBy(4));
        $this->assertTrue($rule->isSatisfiedBy(3));
        $this->assertTrue($rule->isSatisfiedBy(-4));
        $this->assertFalse($rule->isSatisfiedBy(-3));
    }

    public function testFluentXorMethod(): void
    {
        $rule = $this->isPositive()->xor($this->isEven());
        $this->assertInstanceOf(XorBusinessRule::class, $rule);
        $this->assertFalse($rule->isSatisfiedBy(4));  // Both true
        $this->assertTrue($rule->isSatisfiedBy(3));   // Only positive
        $this->assertTrue($rule->isSatisfiedBy(-4));  // Only even
        $this->assertFalse($rule->isSatisfiedBy(-3)); // Neither
    }

    public function testFluentNotMethod(): void
    {
        $rule = $this->isPositive()->not();
        $this->assertInstanceOf(NotBusinessRule::class, $rule);
        $this->assertFalse($rule->isSatisfiedBy(5));
        $this->assertTrue($rule->isSatisfiedBy(-5));
    }

    public function testFluentChaining(): void
    {
        $rule = $this->isPositive()
            ->and($this->isEven())
            ->or($this->alwaysTrue());

        $this->assertTrue($rule->isSatisfiedBy(-3));
    }

    // Code and Message Default Behavior Tests

    public function testDefaultCodeFromClassName(): void
    {
        $rule = new class extends CompositeBusinessRule {
            public function isSatisfiedBy($value): bool
            {
                return true;
            }
        };

        $code = $rule->getCode();
        $this->assertIsString($code);
        $this->assertNotEmpty($code);
    }

    public function testDefaultMessageFromClassName(): void
    {
        $rule = new class extends CompositeBusinessRule {
            public function isSatisfiedBy($value): bool
            {
                return true;
            }
        };

        $message = $rule->getMessage();
        $this->assertIsString($message);
        $this->assertNotEmpty($message);
    }

    public function testCustomCodeConstant(): void
    {
        $rule = new class extends CompositeBusinessRule {
            protected const CODE = 'custom.code.123';

            public function isSatisfiedBy($value): bool
            {
                return true;
            }
        };

        $this->assertEquals('custom.code.123', $rule->getCode());
    }

    public function testCustomMessageConstant(): void
    {
        $rule = new class extends CompositeBusinessRule {
            protected const MESSAGE = 'Custom message here';

            public function isSatisfiedBy($value): bool
            {
                return true;
            }
        };

        $this->assertEquals('Custom message here', $rule->getMessage());
    }

    public function testNumericCode(): void
    {
        $rule = new class extends CompositeBusinessRule {
            protected const CODE = 1001;

            public function isSatisfiedBy($value): bool
            {
                return true;
            }
        };

        $this->assertEquals(1001, $rule->getCode());
    }

    // Real-world scenario tests

    public function testComplexBusinessLogic(): void
    {
        // (Positive AND Even) OR AlwaysTrue
        $rule = $this->isPositive()
            ->and($this->isEven())
            ->or($this->alwaysTrue());

        $this->assertTrue($rule->isSatisfiedBy(4));    // Positive and even
        $this->assertTrue($rule->isSatisfiedBy(-3));   // Neither, but OR with true
        $this->assertTrue($rule->isSatisfiedBy(null)); // OR with true
    }

    public function testNestedComposition(): void
    {
        // NOT (Positive AND Even)
        $rule = $this->isPositive()
            ->and($this->isEven())
            ->not();

        $this->assertFalse($rule->isSatisfiedBy(4));  // Is positive and even
        $this->assertTrue($rule->isSatisfiedBy(3));   // Not even
        $this->assertTrue($rule->isSatisfiedBy(-4));  // Not positive
    }
}

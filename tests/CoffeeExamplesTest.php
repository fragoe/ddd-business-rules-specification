<?php

namespace fragoe\DDDBusinessRules\Tests;

use fragoe\DDDBusinessRules\Examples\Coffee\BrewRecipe;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\AeroPressRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\BrewRatioRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\BrewTimeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\CoffeeAmountRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\ColdBrewRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\EspressoRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\FrenchPressRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\MokaPotRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\PourOverRecipeRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\WaterAmountRule;
use fragoe\DDDBusinessRules\Examples\Coffee\BusinessRules\WaterTemperatureRule;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewMethod;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewRecipeData;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\BrewTime;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\CoffeeAmount;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\GrindSize;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\WaterAmount;
use fragoe\DDDBusinessRules\Examples\Coffee\ValueObjects\WaterTemperature;
use PHPUnit\Framework\TestCase;

/**
 * Comprehensive tests for the Coffee Brewing examples.
 *
 * Tests value objects, business rules, and the BrewRecipe entity.
 * Demonstrates the full coffee brewing domain with educational examples.
 */
class CoffeeExamplesTest extends TestCase
{
    // Value Object Tests

    public function testCoffeeAmountCanBeCreated(): void
    {
        $amount = CoffeeAmount::fromGrams(18);
        $this->assertEquals(18.0, $amount->toGrams());
        $this->assertEquals('18.0g', (string)$amount);
    }

    public function testCoffeeAmountRejectsTooLittle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoffeeAmount::fromGrams(3); // Less than 5g minimum
    }

    public function testCoffeeAmountRejectsTooMuch(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CoffeeAmount::fromGrams(70); // More than 60g maximum
    }

    public function testWaterAmountCanBeCreated(): void
    {
        $amount = WaterAmount::fromMilliliters(300);
        $this->assertEquals(300.0, $amount->toMilliliters());
        $this->assertEquals('300ml', (string)$amount);
    }

    public function testWaterAmountRejectsTooLittle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        WaterAmount::fromMilliliters(20); // Less than 25ml minimum
    }

    public function testWaterTemperatureCanBeCreated(): void
    {
        $temp = WaterTemperature::fromCelsius(93);
        $this->assertEquals(93.0, $temp->toCelsius());
        $this->assertEquals(199.4, $temp->toFahrenheit());
        $this->assertTrue($temp->isHot());
        $this->assertFalse($temp->isCold());
    }

    public function testWaterTemperatureColdDetection(): void
    {
        $coldTemp = WaterTemperature::fromCelsius(20);
        $this->assertTrue($coldTemp->isCold());
        $this->assertFalse($coldTemp->isHot());
    }

    public function testBrewTimeCanBeCreatedFromSeconds(): void
    {
        $time = BrewTime::fromSeconds(180);
        $this->assertEquals(180, $time->toSeconds());
        $this->assertEquals(3.0, $time->toMinutes());
        $this->assertEquals('3min', (string)$time);
    }

    public function testBrewTimeCanBeCreatedFromMinutes(): void
    {
        $time = BrewTime::fromMinutes(4);
        $this->assertEquals(240, $time->toSeconds());
        $this->assertEquals(4.0, $time->toMinutes());
    }

    public function testBrewTimeCanBeCreatedFromHours(): void
    {
        $time = BrewTime::fromHours(16);
        $this->assertEquals(57600, $time->toSeconds());
        $this->assertEquals(16.0, $time->toHours());
        $this->assertEquals('16h', (string)$time);
    }

    public function testGrindSizeHasComparisons(): void
    {
        $this->assertTrue(GrindSize::FINE->isFinerThan(GrindSize::COARSE));
        $this->assertTrue(GrindSize::COARSE->isCoarserThan(GrindSize::FINE));
        $this->assertFalse(GrindSize::MEDIUM->isFinerThan(GrindSize::FINE));
    }

    // Basic Business Rule Tests

    public function testCoffeeAmountRule(): void
    {
        $rule = new CoffeeAmountRule();

        $this->assertTrue($rule->isSatisfiedBy(18)); // Valid
        $this->assertTrue($rule->isSatisfiedBy(5));  // Min
        $this->assertTrue($rule->isSatisfiedBy(60)); // Max

        $this->assertFalse($rule->isSatisfiedBy(4));  // Too low
        $this->assertFalse($rule->isSatisfiedBy(61)); // Too high
        $this->assertFalse($rule->isSatisfiedBy('invalid'));
    }

    public function testWaterAmountRule(): void
    {
        $rule = new WaterAmountRule();

        $this->assertTrue($rule->isSatisfiedBy(300));  // Valid
        $this->assertTrue($rule->isSatisfiedBy(25));   // Min
        $this->assertTrue($rule->isSatisfiedBy(1000)); // Max

        $this->assertFalse($rule->isSatisfiedBy(20));   // Too low
        $this->assertFalse($rule->isSatisfiedBy(1001)); // Too high
    }

    public function testWaterTemperatureRule(): void
    {
        $rule = new WaterTemperatureRule();

        $this->assertTrue($rule->isSatisfiedBy(93));  // Hot brewing
        $this->assertTrue($rule->isSatisfiedBy(20));  // Cold brew
        $this->assertTrue($rule->isSatisfiedBy(0));   // Min
        $this->assertTrue($rule->isSatisfiedBy(100)); // Max

        $this->assertFalse($rule->isSatisfiedBy(-1));  // Below freezing
        $this->assertFalse($rule->isSatisfiedBy(101)); // Above boiling
    }

    public function testBrewTimeRule(): void
    {
        $rule = new BrewTimeRule();

        $this->assertTrue($rule->isSatisfiedBy(25));    // Espresso
        $this->assertTrue($rule->isSatisfiedBy(240));   // French press
        $this->assertTrue($rule->isSatisfiedBy(57600)); // 16h cold brew

        $this->assertFalse($rule->isSatisfiedBy(5));     // Too fast
        $this->assertFalse($rule->isSatisfiedBy(90000)); // Over 24h
    }

    public function testBrewRatioRule(): void
    {
        $rule = new BrewRatioRule();

        // Valid ratios
        $this->assertTrue($rule->isSatisfiedBy(['coffee' => 20, 'water' => 200])); // 1:10
        $this->assertTrue($rule->isSatisfiedBy(['coffee' => 20, 'water' => 300])); // 1:15
        $this->assertTrue($rule->isSatisfiedBy(['coffee' => 20, 'water' => 400])); // 1:20

        // Invalid ratios
        $this->assertFalse($rule->isSatisfiedBy(['coffee' => 20, 'water' => 180])); // 1:9 too strong
        $this->assertFalse($rule->isSatisfiedBy(['coffee' => 20, 'water' => 450])); // 1:22.5 too weak
    }

    // Method-Specific Business Rule Tests

    public function testEspressoRecipeRule(): void
    {
        $rule = new EspressoRecipeRule();

        // Valid espresso
        $validEspresso = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 93,
            brewTime: 27,
            coffee: 18,
            water: 36 // 1:2 ratio
        );
        $this->assertTrue($rule->isSatisfiedBy($validEspresso));

        // Wrong grind (coarse instead of fine)
        $wrongGrind = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 93,
            brewTime: 27,
            coffee: 18,
            water: 36
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongGrind));

        // Wrong temperature (too cold)
        $wrongTemp = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 85,
            brewTime: 27,
            coffee: 18,
            water: 36
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongTemp));

        // Wrong time (too slow)
        $wrongTime = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 93,
            brewTime: 60,
            coffee: 18,
            water: 36
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongTime));

        // Wrong ratio (too diluted)
        $wrongRatio = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 93,
            brewTime: 27,
            coffee: 18,
            water: 100
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongRatio));
    }

    public function testFrenchPressRecipeRule(): void
    {
        $rule = new FrenchPressRecipeRule();

        // Valid French press
        $validFrenchPress = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 94,
            brewTime: 240, // 4 minutes
            coffee: 30,
            water: 480 // 1:16 ratio
        );
        $this->assertTrue($rule->isSatisfiedBy($validFrenchPress));

        // Wrong grind (fine causes over-extraction and sludge)
        $wrongGrind = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 94,
            brewTime: 240,
            coffee: 30,
            water: 480
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongGrind));

        // Wrong time (too fast - under-extracted)
        $wrongTime = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 94,
            brewTime: 120,
            coffee: 30,
            water: 480
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongTime));
    }

    public function testColdBrewRecipeRule(): void
    {
        $rule = new ColdBrewRecipeRule();

        // Valid cold brew
        $validColdBrew = new BrewRecipeData(
            grindSize: GrindSize::EXTRA_COARSE,
            waterTemp: 20, // Room temp
            brewTime: 57600, // 16 hours
            coffee: 60,
            water: 420 // 1:7 ratio (concentrated)
        );
        $this->assertTrue($rule->isSatisfiedBy($validColdBrew));

        // Also valid with coarse grind
        $coarseGrind = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 20,
            brewTime: 57600,
            coffee: 60,
            water: 420
        );
        $this->assertTrue($rule->isSatisfiedBy($coarseGrind));

        // Wrong temperature (too hot for cold brew!)
        $wrongTemp = new BrewRecipeData(
            grindSize: GrindSize::EXTRA_COARSE,
            waterTemp: 90,
            brewTime: 57600,
            coffee: 60,
            water: 420
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongTemp));

        // Wrong time (too fast - needs 12-24 hours)
        $wrongTime = new BrewRecipeData(
            grindSize: GrindSize::EXTRA_COARSE,
            waterTemp: 20,
            brewTime: 7200, // Only 2 hours
            coffee: 60,
            water: 420
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongTime));
    }

    public function testPourOverRecipeRule(): void
    {
        $rule = new PourOverRecipeRule();

        // Valid pour over
        $validPourOver = new BrewRecipeData(
            grindSize: GrindSize::MEDIUM_FINE,
            waterTemp: 93,
            brewTime: 180, // 3 minutes
            coffee: 20,
            water: 320 // 1:16 ratio
        );
        $this->assertTrue($rule->isSatisfiedBy($validPourOver));

        // Also valid with medium grind
        $mediumGrind = new BrewRecipeData(
            grindSize: GrindSize::MEDIUM,
            waterTemp: 93,
            brewTime: 180,
            coffee: 20,
            water: 320
        );
        $this->assertTrue($rule->isSatisfiedBy($mediumGrind));

        // Wrong grind (too coarse)
        $wrongGrind = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 93,
            brewTime: 180,
            coffee: 20,
            water: 320
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongGrind));
    }

    public function testAeroPressRecipeRule(): void
    {
        $rule = new AeroPressRecipeRule();

        // Valid AeroPress (very forgiving!)
        $validAeroPress = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 85,
            brewTime: 90, // 1.5 minutes
            coffee: 17,
            water: 240 // 1:14 ratio
        );
        $this->assertTrue($rule->isSatisfiedBy($validAeroPress));

        // Works with various grinds
        $mediumFine = new BrewRecipeData(
            grindSize: GrindSize::MEDIUM_FINE,
            waterTemp: 85,
            brewTime: 90,
            coffee: 17,
            water: 240
        );
        $this->assertTrue($rule->isSatisfiedBy($mediumFine));

        $medium = new BrewRecipeData(
            grindSize: GrindSize::MEDIUM,
            waterTemp: 85,
            brewTime: 90,
            coffee: 17,
            water: 240
        );
        $this->assertTrue($rule->isSatisfiedBy($medium));

        // But not coarse
        $wrongGrind = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 85,
            brewTime: 90,
            coffee: 17,
            water: 240
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongGrind));
    }

    public function testMokaPotRecipeRule(): void
    {
        $rule = new MokaPotRecipeRule();

        // Valid Moka Pot
        $validMokaPot = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 80,
            brewTime: 240, // 4 minutes
            coffee: 20,
            water: 160 // 1:8 ratio
        );
        $this->assertTrue($rule->isSatisfiedBy($validMokaPot));

        // Also valid with medium-fine grind
        $mediumFineGrind = new BrewRecipeData(
            grindSize: GrindSize::MEDIUM_FINE,
            waterTemp: 80,
            brewTime: 240,
            coffee: 20,
            water: 160
        );
        $this->assertTrue($rule->isSatisfiedBy($mediumFineGrind));

        // Wrong grind (too coarse)
        $wrongGrind = new BrewRecipeData(
            grindSize: GrindSize::COARSE,
            waterTemp: 80,
            brewTime: 240,
            coffee: 20,
            water: 160
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongGrind));

        // Wrong time (too fast)
        $wrongTime = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 80,
            brewTime: 60, // Only 1 minute
            coffee: 20,
            water: 160
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongTime));

        // Wrong ratio (too diluted)
        $wrongRatio = new BrewRecipeData(
            grindSize: GrindSize::FINE,
            waterTemp: 80,
            brewTime: 240,
            coffee: 20,
            water: 300 // 1:15 - too weak for Moka Pot
        );
        $this->assertFalse($rule->isSatisfiedBy($wrongRatio));
    }

    // BrewRecipe Entity Tests

    public function testCanCreateValidEspressoRecipe(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::ESPRESSO,
            coffeeGrams: 18,
            waterMilliliters: 36,
            grindSize: GrindSize::FINE,
            waterCelsius: 93,
            brewSeconds: 27,
            notes: 'Classic double espresso'
        );

        $this->assertEquals(BrewMethod::ESPRESSO, $recipe->getMethod());
        $this->assertEquals(18.0, $recipe->getCoffeeAmount()->toGrams());
        $this->assertEquals(36.0, $recipe->getWaterAmount()->toMilliliters());
        $this->assertEquals(GrindSize::FINE, $recipe->getGrindSize());
        $this->assertEquals(93.0, $recipe->getWaterTemperature()->toCelsius());
        $this->assertEquals(27, $recipe->getBrewTime()->toSeconds());
        $this->assertEquals(2.0, $recipe->getBrewRatio());
    }

    public function testCanCreateValidFrenchPressRecipe(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::FRENCH_PRESS,
            coffeeGrams: 30,
            waterMilliliters: 480,
            grindSize: GrindSize::COARSE,
            waterCelsius: 94,
            brewSeconds: 240
        );

        $this->assertEquals(BrewMethod::FRENCH_PRESS, $recipe->getMethod());
        $this->assertEquals(16.0, $recipe->getBrewRatio());
    }

    public function testCanCreateValidColdBrewRecipe(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::COLD_BREW,
            coffeeGrams: 60,
            waterMilliliters: 420,
            grindSize: GrindSize::EXTRA_COARSE,
            waterCelsius: 20,
            brewSeconds: 57600, // 16 hours
            notes: 'Refrigerated overnight, dilute 1:1 when serving'
        );

        $this->assertEquals(BrewMethod::COLD_BREW, $recipe->getMethod());
        $this->assertTrue($recipe->getWaterTemperature()->isCold());
        $this->assertEquals(16.0, $recipe->getBrewTime()->toHours());
    }

    public function testCanCreateValidPourOverRecipe(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::POUR_OVER,
            coffeeGrams: 20,
            waterMilliliters: 320,
            grindSize: GrindSize::MEDIUM_FINE,
            waterCelsius: 93,
            brewSeconds: 180
        );

        $this->assertEquals(BrewMethod::POUR_OVER, $recipe->getMethod());
        $this->assertEquals(16.0, $recipe->getBrewRatio());
    }

    public function testCanCreateValidAeroPressRecipe(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::AEROPRESS,
            coffeeGrams: 17,
            waterMilliliters: 240,
            grindSize: GrindSize::FINE,
            waterCelsius: 85,
            brewSeconds: 90
        );

        $this->assertEquals(BrewMethod::AEROPRESS, $recipe->getMethod());
        $this->assertEqualsWithDelta(14.1, $recipe->getBrewRatio(), 0.1);
    }

    public function testCanCreateValidMokaPotRecipe(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::MOKA_POT,
            coffeeGrams: 20,
            waterMilliliters: 160,
            grindSize: GrindSize::FINE,
            waterCelsius: 80,
            brewSeconds: 240,
            notes: 'Classic Italian stovetop coffee'
        );

        $this->assertEquals(BrewMethod::MOKA_POT, $recipe->getMethod());
        $this->assertEquals(8.0, $recipe->getBrewRatio());
        $this->assertEquals('4min', (string)$recipe->getBrewTime());
    }

    public function testEspressoWithWrongGrindFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/espresso/i');

        BrewRecipe::create(
            method: BrewMethod::ESPRESSO,
            coffeeGrams: 18,
            waterMilliliters: 36,
            grindSize: GrindSize::COARSE, // Wrong! Should be FINE
            waterCelsius: 93,
            brewSeconds: 27
        );
    }

    public function testFrenchPressWithWrongGrindFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/french-press/i');

        BrewRecipe::create(
            method: BrewMethod::FRENCH_PRESS,
            coffeeGrams: 30,
            waterMilliliters: 480,
            grindSize: GrindSize::FINE, // Wrong! Should be COARSE
            waterCelsius: 94,
            brewSeconds: 240
        );
    }

    public function testColdBrewWithHotWaterFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/cold-brew/i');

        BrewRecipe::create(
            method: BrewMethod::COLD_BREW,
            coffeeGrams: 60,
            waterMilliliters: 420,
            grindSize: GrindSize::EXTRA_COARSE,
            waterCelsius: 95, // Wrong! Should be 4-22°C
            brewSeconds: 57600
        );
    }

    public function testColdBrewWithShortTimeFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/cold-brew/i');

        BrewRecipe::create(
            method: BrewMethod::COLD_BREW,
            coffeeGrams: 60,
            waterMilliliters: 420,
            grindSize: GrindSize::EXTRA_COARSE,
            waterCelsius: 20,
            brewSeconds: 3600 // Wrong! Only 1 hour, needs 12-24
        );
    }

    public function testMokaPotWithWrongGrindFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/moka-pot/i');

        BrewRecipe::create(
            method: BrewMethod::MOKA_POT,
            coffeeGrams: 20,
            waterMilliliters: 160,
            grindSize: GrindSize::COARSE, // Wrong! Should be FINE or MEDIUM_FINE
            waterCelsius: 80,
            brewSeconds: 240
        );
    }

    public function testBrewRecipeProducesSummary(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::POUR_OVER,
            coffeeGrams: 20,
            waterMilliliters: 320,
            grindSize: GrindSize::MEDIUM_FINE,
            waterCelsius: 93,
            brewSeconds: 180
        );

        $summary = $recipe->getSummary();
        $this->assertStringContainsString('Pour Over', $summary);
        $this->assertStringContainsString('20.0g', $summary);
        $this->assertStringContainsString('320ml', $summary);
        $this->assertStringContainsString('1:16', $summary);
        $this->assertStringContainsString('Medium-Fine', $summary);
        $this->assertStringContainsString('93', $summary);
        $this->assertStringContainsString('3min', $summary);
    }

    public function testBrewRecipeCanUpdateNotes(): void
    {
        $recipe = BrewRecipe::create(
            method: BrewMethod::ESPRESSO,
            coffeeGrams: 18,
            waterMilliliters: 36,
            grindSize: GrindSize::FINE,
            waterCelsius: 93,
            brewSeconds: 27
        );

        $this->assertNull($recipe->getNotes());

        $recipe->updateNotes('Hints of chocolate and caramel, balanced acidity');
        $this->assertEquals('Hints of chocolate and caramel, balanced acidity', $recipe->getNotes());
    }
}

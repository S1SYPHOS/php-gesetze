<?php

namespace S1SYPHOS\Gesetze\Tests\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver\Buzer;

use Exception;


/**
 * Class BuzerTest
 *
 * Adds tests for class 'Buzer'
 */
class BuzerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * Instance
     *
     * @var \S1SYPHOS\Gesetze\Drivers\Driver\Buzer
     */
    private static $object;


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Instance
        self::$object = new Buzer;
    }


    /**
     * Tests
     */

    public function testValidate(): void
    {
        # Setup
        # (1) Legal norms
        $norms = [
            '§ 433 BGB' => true,
            '§ 1a BGB' => false,
        ];

        # Run function
        foreach ($norms as $norm => $expected) {
            # Assert result
            $this->assertEquals(self::$object->validate($norm), $expected);
        }
    }


    public function testValidateEmpty(): void
    {
        # Setup
        # (1) Norms
        $norms = [
            '',
            '§ 1 by itself == useless',
            'This is for educational purposes only',
        ];

        # Run function
        foreach ($norms as $norm) {
            # Assert result
            $this->assertFalse(self::$object->validate($norm));
        }
    }


    public function testBuildAttributes(): void
    {
        # Setup
        # (1) Norms ..
        $norms = [
            # .. as string
            '§ 433 BGB',

            # .. as array
            ['norm'   => '433', 'gesetz' => 'BGB'],
        ];

        foreach ($norms as $norm) {
            # Assert result
            $this->assertIsArray(self::$object->buildAttributes($norm));
        }
    }
}

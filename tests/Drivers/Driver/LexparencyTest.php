<?php

namespace S1SYPHOS\Gesetze\Tests\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver\Lexparency;

use Exception;


/**
 * Class LexparencyTest
 *
 * Adds tests for class 'Lexparency'
 */
class LexparencyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Properties
     */

    /**
     * Instance
     *
     * @var \S1SYPHOS\Gesetze\Drivers\Driver\Lexparency
     */
    private static $object;


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Instance
        self::$object = new Lexparency;
    }


    /**
     * Tests
     */

    public function testValidate(): void
    {
        # Setup
        # (1) Legal norms
        $norms = [
            'Art. 6 DSGVO' => true,
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
            'Art. 6 DSGVO',

            # .. as array
            ['norm'   => '6', 'gesetz' => 'DSGVO'],
        ];

        foreach ($norms as $norm) {
            # Assert result
            $this->assertIsArray(self::$object->buildAttributes($norm));
        }
    }
}

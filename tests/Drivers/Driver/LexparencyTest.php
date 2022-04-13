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
        # (1) Norms
        $norms = [
            ['norm' => '1', 'gesetz' => 'ZZZZZ'],
            ['norm' => '1aa', 'gesetz' => 'BGB'],
            ['norm' => '6', 'gesetz' => 'DSGVO'],
        ];

        # Run function
        foreach ($norms as $norm) {
            # Assert result
            $this->assertIsBool(self::$object->validate($norm));
        }
    }


    public function testBuildTitle(): void
    {
        # Setup
        # (1) Norms
        $norm = [
            'norm'   => '6',
            'gesetz' => 'DSGVO',
        ];

        # Assert result
        $this->assertIsString(self::$object->buildTitle($norm));
    }


    public function testBuildTitleInvalid(): void
    {
        # Setup
        # (1) Norm
        $norm = [
            'norm'   => '9999',
            'gesetz' => 'ZZZZ',
        ];

        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        self::$object->buildTitle($norm);
    }


    public function testBuildURL(): void
    {
        # Setup
        # (1) Norm
        $norm = [
            'norm'   => '6',
            'gesetz' => 'DSGVO',
        ];

        # Assert result
        $this->assertIsString(self::$object->buildURL($norm));
    }


    public function testBuildURLInvalid(): void
    {
        # Setup
        # (1) Norm
        $norm = [
            'norm'   => '9999',
            'gesetz' => 'ZZZZ',
        ];

        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        self::$object->buildURL($norm);
    }
}

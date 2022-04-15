<?php

namespace S1SYPHOS\Gesetze\Tests\Drivers;

use S1SYPHOS\Gesetze\Drivers\Factory;

use Exception;


/**
 * Class FactoryTest
 *
 * Adds tests for class 'Factory'
 */
class DriversTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testCreate(): void
    {
        # Setup
        # (1) Providers
        $drivers = [
            'gesetze'    => 'S1SYPHOS\Gesetze\Drivers\Driver\GesetzeImInternet',
            'dejure'     => 'S1SYPHOS\Gesetze\Drivers\Driver\DejureOnline',
            'buzer'      => 'S1SYPHOS\Gesetze\Drivers\Driver\Buzer',
            'lexparency' => 'S1SYPHOS\Gesetze\Drivers\Driver\Lexparency',
        ];

        foreach ($drivers as $driver => $className) {
            # Run function
            $result = Factory::create($driver);

            # Assert result
            $this->assertInstanceOf($className, $result);
        }
    }


    public function testCreateInvalid(): void
    {
        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        Factory::create('?!#@=');
    }
}

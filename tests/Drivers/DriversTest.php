<?php

namespace S1SYPHOS\Gesetze\Tests\Drivers;

use S1SYPHOS\Gesetze\Drivers\Drivers;

use Exception;


/**
 * Class DriversTest
 *
 * Adds tests for class 'Drivers'
 */
class DriversTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testFactory(): void
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
            $result = Drivers::factory($driver);

            # Assert result
            $this->assertInstanceOf($className, $result);
        }
    }


    public function testFactoryInvalid(): void
    {
        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        Drivers::factory('?!#@=');
    }
}

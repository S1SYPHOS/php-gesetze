<?php

namespace S1SYPHOS\Gesetze\Tests\Providers;

use S1SYPHOS\Gesetze\Providers\Factory;

use Exception;


/**
 * Class FactoryTest
 *
 * Adds tests for class 'Factory'
 */
class ProvidersTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testCreate(): void
    {
        # Setup
        # (1) Providers
        $drivers = [
            'gesetze'    => 'S1SYPHOS\Gesetze\Providers\Provider\GesetzeImInternet',
            'dejure'     => 'S1SYPHOS\Gesetze\Providers\Provider\DejureOnline',
            'buzer'      => 'S1SYPHOS\Gesetze\Providers\Provider\Buzer',
            'lexparency' => 'S1SYPHOS\Gesetze\Providers\Provider\Lexparency',
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

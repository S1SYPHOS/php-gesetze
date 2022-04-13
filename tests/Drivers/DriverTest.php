<?php

namespace S1SYPHOS\Gesetze\Tests\Drivers;

use Exception;


/**
 * Class AbstractDriverTest
 *
 * Mocks abstract class 'Driver'
 */
class AbstractDriverTest extends \S1SYPHOS\Gesetze\Drivers\Driver {
    /**
     * Methods
     */

    /**
     * Builds URL for corresponding legal norm
     *
     * Used as `href` attribute
     *
     * @param array $array Formatted regex match
     * @return string
     */
    public function buildURL(array $array): string {}
}


/**
 * Class DriverTest
 *
 * Adds tests for abstract class 'Driver'
 */
class DriverTest extends \PHPUnit\Framework\TestCase
{
    public function testInitInvalid(): void
    {
        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        new AbstractDriverTest(__DIR__ . '/invalid.json');
    }
}

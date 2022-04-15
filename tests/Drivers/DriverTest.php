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
     * @param string|array $string Matched text OR formatted regex match
     * @return string
     */
    public function buildURL($data): string {}
}


/**
 * Class DriverTest
 *
 * Adds tests for abstract class 'Driver'
 */
class DriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testInitInvalid(): void
    {
        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        new AbstractDriverTest(__DIR__ . '/invalid.json');
    }
}

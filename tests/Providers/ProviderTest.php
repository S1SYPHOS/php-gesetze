<?php

namespace S1SYPHOS\Gesetze\Tests\Providers;

use Exception;


/**
 * Class AbstractProviderTest
 *
 * Mocks abstract class 'Provider'
 */
class AbstractProviderTest extends \S1SYPHOS\Gesetze\Providers\Provider {
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
    public function getURL($data): string {}
}


/**
 * Class ProviderTest
 *
 * Adds tests for abstract class 'Provider'
 */
class ProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testInitInvalid(): void
    {
        # Assert exception
        $this->expectException(Exception::class);

        # Run function
        new AbstractProviderTest(__DIR__ . '/invalid.json');
    }
}

<?php

namespace S1SYPHOS\Gesetze\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver;


/**
 * Class DejureOnline
 *
 * Driver for 'dejure.org'
 */
class DejureOnline extends Driver
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = 'dejure';


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
     * @throws \Exception
     */
    protected function buildURL($data): string {
        # Get lowercase identifier for current law
        $identifier = strtolower($data['gesetz']);

        # Combine everything
        return sprintf('https://dejure.org/gesetze/%s/%s',
            $this->library[$identifier]['slug'], $data['norm'] . '.html'
        );
    }
}

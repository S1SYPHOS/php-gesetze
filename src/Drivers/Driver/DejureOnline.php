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
     * @param array $array Formatted regex match
     * @return string
     */
    public function buildURL(array $array): string {
        # Get lowercase identifier for current law
        $identifier = strtolower($array['gesetz']);

        # Combine everything
        return sprintf('https://dejure.org/gesetze/%s/%s',
            $this->library[$identifier]['slug'], $array['norm'] . '.html'
        );
    }
}

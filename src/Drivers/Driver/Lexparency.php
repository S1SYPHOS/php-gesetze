<?php

namespace S1SYPHOS\Gesetze\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver;


/**
 * Class Lexparency
 *
 * Driver for 'lexparency.de'
 */
class Lexparency extends Driver
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = 'lexparency';


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
    protected function buildURL($data): string {
        # Get lowercase identifier for current law
        $identifier = strtolower($data['gesetz']);

        # Combine everything
        return sprintf('https://lexparency.de/eu/%s/%s',
            $this->library[$identifier]['slug'], 'ART_' . $data['norm']
        );
    }
}

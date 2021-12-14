<?php

namespace S1SYPHOS\Gesetze\Drivers;


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
     * @param array $array Formatted regex match
     * @return string
     */
    public function buildURL(array $array): string {
        # Get lowercase identifier for current law
        $identifier = strtolower($array['gesetz']);

        # Combine everything
        return sprintf('https://lexparency.de/eu/%s/%s',
            $this->library[$identifier]['slug'], 'ART_' . $array['norm']
        );
    }
}

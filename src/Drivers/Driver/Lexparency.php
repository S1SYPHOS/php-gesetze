<?php

namespace S1SYPHOS\Gesetze\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver;

use Exception;


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
     * @throws \Exception
     */
    public function buildURL(array $array): string {
        # Get lowercase identifier for current law
        $identifier = strtolower($array['gesetz']);

        # Fail early if law is unavailable
        if (!isset($this->library[$identifier])) {
            throw new Exception(sprintf('Invalid law: "%s"', $array['gesetz']));
        }

        # Combine everything
        return sprintf('https://lexparency.de/eu/%s/%s',
            $this->library[$identifier]['slug'], 'ART_' . $array['norm']
        );
    }
}

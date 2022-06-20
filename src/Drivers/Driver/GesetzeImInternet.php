<?php

namespace S1SYPHOS\Gesetze\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver;


/**
 * Class GesetzeImInternet
 *
 * Driver for 'gesetze-im-internet.de'
 */
class GesetzeImInternet extends Driver
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = 'gesetze';


    /**
     * Methods
     */

    /**
     * Builds URL for corresponding legal norm
     *
     * Used as `href` attribute
     *
     * @param string|array $data Matched text OR formatted regex match
     * @return string
     */
    protected function buildURL($data): string {
        # Examine input
        if (is_string($data)) {
            $data = $this->analyze($data);
        }

        # If something goes south ..
        if (!in_array('gesetz', array_keys($data))) {
            # .. fail early
            return '';
        }

        # Get lowercase identifier for current law
        $identifier = strtolower($data['gesetz']);

        # Set default HTML file
        $file = '__' . $data['norm'] . '.html';

        # Except for the 'Grundgesetz' ..
        if (strtolower($data['gesetz']) === 'gg') {
            # .. which is different
            $file = 'art_' . $data['norm'] . '.html';
        }

        # Combine everything
        return sprintf('https://www.gesetze-im-internet.de/%s/%s',
            $this->library[$identifier]['slug'], $file
        );
    }
}

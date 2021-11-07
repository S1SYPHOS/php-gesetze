<?php

namespace S1SYPHOS\Gesetze\Drivers;


/**
 * Class DejureOnline
 *
 * Driver for 'dejure.org'
 */
class DejureOnline extends Driver
{
    /**
     * Traits
     */

    use \S1SYPHOS\Gesetze\Traits\Title;


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

        # Get data about current law
        $law = $this->library[$identifier];

        # Set base URL
        $url = 'https://dejure.org/gesetze';

        # Set HTML file
        $file = $array['norm'] . '.html';

        # Combine everything
        return sprintf('%s/%s/%s', $url, $law['slug'], $file);
    }
}

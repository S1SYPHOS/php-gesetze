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

        # Get data about current law
        $law = $this->library[$identifier];

        # Set base URL
        $url = 'https://lexparency.de/eu';

        # Set HTML file
        $file = 'ART_' . $array['norm'];

        # Combine everything
        return sprintf('%s/%s/%s', $url, $law['slug'], $file);
    }
}

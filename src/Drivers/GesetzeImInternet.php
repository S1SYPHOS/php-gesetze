<?php

namespace S1SYPHOS\Gesetze\Drivers;


/**
 * Class GesetzeImInternet
 *
 * Driver for 'gesetze-im-internet.de'
 */
class GesetzeImInternet extends Driver
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
    protected $identifier = 'gesetze';


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
        $url = 'https://www.gesetze-im-internet.de';

        # Set default HTML file
        $file = '__' . $array['norm'] . '.html';

        # Except for the 'Grundgesetz' ..
        if (strtolower($array['gesetz']) === 'gg') {
            # .. which is different
            $file = 'art_' . $array['norm'] . '.html';
        }

        # Combine everything
        return sprintf('%s/%s/%s', $url, $law['slug'], $file);
    }
}

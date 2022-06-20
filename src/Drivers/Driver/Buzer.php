<?php

namespace S1SYPHOS\Gesetze\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver;


/**
 * Class Buzer
 *
 * Driver for 'buzer.de'
 */
class Buzer extends Driver
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = 'buzer';


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

        # Combine everything
        return sprintf('https://buzer.de/%s',
            $this->library[$identifier]['headings'][$data['norm']]['slug']
        );
    }


    /**
     * Builds description for corresponding legal norm
     *
     * Used as `title` attribute
     *
     * @param string|array $data Matched text OR formatted regex match
     * @param string|false $mode Mode of operation
     * @return string
     */
    protected function buildTitle($data, $mode = null): string
    {
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

        # Get data about current law
        $law = $this->library[$identifier];

        # Determine `title` attribute
        switch ($mode) {
            case 'light':
                return $law['law'];

            case 'normal':
                return $law['title'];

            case 'full':
                return $law['headings'][$data['norm']]['text'];
        }

        return '';
    }
}

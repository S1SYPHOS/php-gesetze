<?php

namespace S1SYPHOS\Gesetze\Drivers\Driver;

use S1SYPHOS\Gesetze\Drivers\Driver;

use Exception;


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
        return sprintf('https://buzer.de/%s',
            $this->library[$identifier]['headings'][$array['norm']]['slug']
        );
    }


    /**
     * Builds description for corresponding legal norm
     *
     * Used as `title` attribute
     *
     * @param array $array Formatted regex match
     * @param mixed $mode Mode of operation
     * @return string
     * @throws \Exception
     */
    public function buildTitle(array $array, $mode = null): string
    {
        # Get lowercase identifier for current law
        $identifier = strtolower($array['gesetz']);

        # Fail early if law is unavailable
        if (!isset($this->library[$identifier])) {
            throw new Exception(sprintf('Invalid law: "%s"', $array['gesetz']));
        }

        # Get data about current law
        $law = $this->library[$identifier];

        # Determine `title` attribute
        switch ($mode) {
            case 'light':
                return $law['law'];

            case 'normal':
                return $law['title'];

            case 'full':
                return $law['headings'][$array['norm']]['text'];
        }

        return '';
    }
}

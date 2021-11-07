<?php

namespace S1SYPHOS\Gesetze\Traits;


/**
 * Trait Title
 *
 * Provides default implementation for building `title` attribute
 */
trait Title
{
    /**
     * Methods
     */

    /**
     * Builds description for corresponding legal norm
     *
     * Used as `title` attribute
     *
     * @param array $array Formatted regex match
     * @param mixed $mode Mode of operation
     * @return string
     */
    public function buildTitle(array $array, $mode): string
    {
        # Get lowercase identifier for current law
        $identifier = strtolower($array['gesetz']);

        # Get data about current law
        $law = $this->library[$identifier];

        # Determine `title` attribute
        switch ($mode) {
            case 'light':
                return $law['law'];

            case 'normal':
                return $law['title'];

            case 'full':
                return $law['headings'][$array['norm']];
        }

        return '';
    }
}

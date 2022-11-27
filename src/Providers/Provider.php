<?php

namespace S1SYPHOS\Gesetze\Providers;

use S1SYPHOS\Gesetze\Traits\Regex;

use Exception;


/**
 * Class Provider
 *
 * Base template for drivers
 */
abstract class Provider
{
    /**
     * Traits
     */

    use Regex;


    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = '';


    /**
     * Base URL
     *
     * @var string
     */
    protected $url = '';


    /**
     * Available laws
     *
     * @var array
     */
    protected $library;


    /**
     * Constructor
     *
     * @param string $file Path to JSON index file
     * @return void
     * @throws \Exception
     */
    public function __construct(?string $file = null)
    {
        # Set default index file
        if (is_null($file)) {
            $file = sprintf('%s/../../data/%s.json', __DIR__, $this->identifier);
        }

        # Fail early if file does not exist
        if (!file_exists($file)) {
            throw new Exception(sprintf('File does not exist: "%s"', realpath($file)));
        }

        # Load law library data
        $this->library = json_decode(file_get_contents($file), true);
    }


    /**
     * Methods
     */

    /**
     * Validates a single legal norm
     *
     * @param string|array $data Matched text OR formatted regex match
     * @return bool Validity of legal norm
     */
    public function validate($data): bool
    {
        # Examine input
        if (is_string($data)) {
            $data = $this->analyze($data);
        }

        # Fail early if match is empty
        if (empty($data)) {
            return false;
        }

        # Get lowercase identifier for current law
        $identifier = strtolower($data['gesetz']);

        # Check whether current law exists in library ..
        if (!isset($this->library[$identifier])) {
            # .. otherwise fail check
            return false;
        }

        # Get data about current law
        $law = $this->library[$identifier];

        # Since `norm` is always a string ..
        $norm = $data['norm'];

        # .. but PHP decodes JSON numeric keys as integer ..
        if (preg_match('/\b\d+\b/', $norm)) {
            # .. convert them first
            $norm = (int)$norm;
        }

        return in_array($norm, array_map('strval', array_keys($law['norms'])));
    }


    /**
     * Retrieves URL for corresponding legal norm
     *
     * Used as `href` attribute
     *
     * @param string|array $data Matched text OR formatted regex match
     * @return string
     */
    protected function getURL($data): string
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

        return sprintf('%s/%s', $this->url, $law['norms'][$data['norm']]['uri']);
    }


    /**
     * Retrieves description for corresponding legal norm
     *
     * Used as `title` attribute
     *
     * @param string|array $data Matched text OR formatted regex match
     * @param string|false $mode Mode of operation
     * @return string
     */
    protected function getTitle($data, $mode = null): string
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
                return $law['norms'][$data['norm']]['title'];
        }

        return '';
    }


    /**
     * Builds HTML attributes for corresponding legal norm
     *
     * @param string|array $data Matched text OR formatted regex match
     * @param string|false $mode Mode of operation
     * @return array
     */
    public function buildAttributes($data, $mode = null): array
    {
        # Examine input
        if (is_string($data)) {
            $data = $this->analyze($data);
        }

        # Fail early if match is empty
        if (empty($data)) {
            return [];
        }

        return [
            'href'  => $this->getURL($data),
            'title' => $this->getTitle($data, $mode),
        ];
    }
}

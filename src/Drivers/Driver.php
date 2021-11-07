<?php

namespace S1SYPHOS\Gesetze\Drivers;


/**
 * Class Driver
 *
 * Base template for drivers
 */
abstract class Driver
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = null;


    /**
     * Available laws
     *
     * @var array
     */
    protected $library;


    /**
     * Constructor
     *
     * @return void
     * @throws \Exception
     */
    public function __construct()
    {
        # Determine data file
        $file = sprintf('%s/../../laws/%s.json', __DIR__, $this->identifier);

        if (!file_exists($file)) {
            throw new \Exception(sprintf('File does not exist: "%s"', realpath($file)));
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
     * @param array $array Formatted regex match
     * @return bool Validity of legal norm
     */
    public function validate(array $array): bool
    {
        # Get lowercase identifier for current law
        $identifier = strtolower($array['gesetz']);

        # Check whether current law exists in library ..
        if (!isset($this->library[$identifier])) {
            # .. otherwise fail check
            return false;
        }

        # Get data about current law
        $law = $this->library[$identifier];

        # Since `norm` is always a string ..
        $norm = $array['norm'];

        # .. but PHP decodes JSON numeric keys as integer ..
        if (preg_match('/\b\d+\b/', $norm)) {
            # .. convert them first
            $norm = (int)$norm;
        }

        return in_array($norm, array_map('strval', array_keys($law['headings'])));
    }


    /**
     * Builds URL for corresponding legal norm
     *
     * Used as `href` attribute
     *
     * @param array $array Formatted regex match
     * @return string
     */
    abstract protected function buildURL(array $array): string;


    /**
     * Builds description for corresponding legal norm
     *
     * Used as `title` attribute
     *
     * @param array $array Formatted regex match
     * @param mixed $mode Mode of operation
     * @return string
     */
    abstract protected function buildTitle(array $array, $mode): string;
}

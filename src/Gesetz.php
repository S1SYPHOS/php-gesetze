<?php

/**
 * Linking german legal norms, dependency-free & GDPR-friendly.
 *
 * @link https://codeberg.org/S1SYPHOS/php-gesetze
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 0.7.0
 */

namespace S1SYPHOS\Gesetze;

use S1SYPHOS\Gesetze\Drivers\Factory;
use S1SYPHOS\Gesetze\Traits\Regex;

use Exception;


/**
 * Class Gesetz
 *
 * Utilities for dealing with german legal norms
 */
class Gesetz
{
    /**
     * Traits
     */

    use Regex;


    /**
     * Properties
     */

    /**
     * Available providers
     *
     * @var array
     */
    public $drivers = [];


    /**
     * Defines HTML attributes
     *
     * @var array
     */
    public $attributes = [];


    /**
     * Controls `title` attribute
     *
     * Possible values:
     *
     * 'light'  => abbreviated law (eg 'GG')
     * 'normal' => complete law (eg 'Grundgesetz')
     * 'full'   => official heading (eg 'Art 45d Parlamentarisches Kontrollgremium')
     *
     * @var string|false
     */
    public $title = false;


    /**
     * Constructor
     *
     * @param string|array $drivers Identifiers of providers to be used
     * @return void
     * @throws \Exception
     */
    public function __construct($drivers = null)
    {
        # Set default order
        if (is_null($drivers)) {
            $drivers = [
                'gesetze',     # 'gesetze-im-internet.de'
                'dejure',      # 'dejure.org'
                'buzer',       # 'buzer.de'
                'lexparency',  # 'lexparency.de'
            ];
        }

        # If string was passed as order ..
        if (is_string($drivers)) {
            # .. make it an array
            $drivers = [$drivers];
        }

        # Loop through selected drivers
        foreach ($drivers as $driver) {
            # Initialize each of them
            $this->drivers[$driver] = Factory::create($driver);
        }
    }


    /**
     * Methods
     */

    /**
     * Validates a single legal norm (across all providers)
     *
     * @param string $string Legal norm
     * @return bool Whether legal norm is valid (with regard to its 'linkability')
     */
    public function validate(string $string): bool
    {
        # Fail early when string is empty
        if (empty($string)) {
            return false;
        }

        # Iterate over drivers
        foreach ($this->drivers as $driver => $object) {
            # If legal norm checks out ..
            if ($object->validate($string)) {
                # .. break the loop
                return true;
            }
        }

        return false;
    }


    /**
     * Extracts legal norms as array of strings
     *
     * @param string $string Text
     * @return array
     */
    public function extract(string $string): array
    {
        preg_match_all($this->pattern, $string, $matches);

        return $matches[0];
    }


    /**
     * Converts matched legal reference into `a` tag
     *
     * @param array $match Matched legal norm
     * @return string
     */
    private function linkify(array $match): string
    {
        # Create data array
        $attributes = [];

        # Fetch extracted data
        $data = $this->groupMatch($match);

        # Iterate over drivers for each match ..
        foreach ($this->drivers as $driver => $object) {
            # .. blocking invalid laws & legal norms
            if (!$object->validate($data)) {
                continue;
            }

            # Build `a` tag attributes
            $attributes = array_merge($this->attributes, $object->buildAttributes($data, $this->title));

            # Abort the loop
            break;
        }

        # If something goes south ..
        if (!in_array('href', array_keys($attributes))) {
            # .. return original text
            return $match[0];
        }

        # If URL not found ..
        if (empty($attributes['href'])) {
            # .. return original text
            return $match[0];
        }

        # Build `a` tag
        # (1) Format key-value pairs
        $attributes = array_map(function($key, $value) {
            return sprintf('%s="%s"', $key, $value);
        }, array_keys($attributes), array_values($attributes));

        # (2) Combine everything
        return '<a ' . implode(' ', $attributes) . '>' . $match[0] . '</a>';
    }


    /**
     * Converts legal references throughout text into `a` tags
     *
     * @param string $string Unprocessed text
     * @param callable $callback Callback function
     * @return string Processed text
     */
    public function gesetzify(string $string, ?callable $callback = null): string
    {
        if (is_null($callback)) {
            $callback = [$this, 'linkify'];
        }

        return preg_replace_callback($this->pattern, $callback, $string);
    }


    /**
     * Converts roman numerals to arabic numerals
     *
     * @param string $string
     * @return int
     * @throws \Exception
     */
    public static function roman2arabic(string $string)
    {
        # If one of the characters represents an invalid roman numeral ..
        if (!preg_match('/[IVXLCDM]+/i', $string)) {
            # .. throw error
            throw new Exception('Input contains invalid character.');
        }

        # Transform string to uppercase
        $string = strtoupper($string);

        # Map roman numerals to their arabic equivalent
        # See https://stackoverflow.com/a/6266158
        $romans = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1,
        ];

        $result = 0;

        foreach ($romans as $key => $value) {
            while (strpos($string, $key) === 0) {
                $result += $value;
                $string = substr($string, strlen($key));
            }
        }

        return $result;
    }
}

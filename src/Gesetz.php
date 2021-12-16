<?php

/**
 * Linking german legal norms, dependency-free & GDPR-friendly.
 *
 * @link https://github.com/S1SYPHOS/php-gesetze
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 0.4.2
 */

namespace S1SYPHOS\Gesetze;


/**
 * Class Gesetz
 *
 * Utilities for dealing with german legal norms
 */
class Gesetz
{
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
     * Blocked providers
     *
     * @var array
     */
    public $blockList = [];


    /**
     * The regex, holding the world together in its inmost folds
     *
     * For reference:
     *
     * '/(?:§+|Art\.?|Artikel)\s*(\d+(?:\w\b)?)\s*(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*(?:(?:S\.|Satz)\s*(\d+))?\s*(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*(?:(?:lit\.|litera|Buchst\.|Buchstabe)\s*([a-z]?))?.{0,10}?(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?\b)/'
     */
    public static $pattern = ''
        # Start
        . '/'
        # Section sign
        . '(?:(?:§)+|Art\.?|Artikel)\s*'
        # Section ('Norm')
        . '(\d+(?:\w\b)?)\s*'
        # Subsection ('Absatz')
        . '(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*'
        # Sentence ('Satz')
        . '(?:(?:S\.|Satz)\s*(\d+))?\s*'
        # Number ('Nummer')
        . '(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*'
        # Letter ('Litera')
        . '(?:(?:lit\.|litera|Buchst\.|Buchstabe)\s*([a-z]?))?'
        # Character limit
        . '.{0,10}?'
        # Law ('Gesetz')
        . '(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?\b)'
        # End
        . '/';


    /**
     * Names for capturing groups
     *
     * @var array
     */
    private static $groups = [
        'norm',
        'absatz',
        'satz',
        'nr',
        'lit',
        'gesetz',
    ];


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
     * @var mixed string|false
     */
    public $title = false;


    /**
     * Constructor
     *
     * @param mixed $driver Provider identifier
     * @return void
     */
    public function __construct($order = 'gesetze')
    {
        # List available drivers
        $drivers = [
            # (1) 'gesetze-im-internet.de'
            'gesetze' => '\S1SYPHOS\Gesetze\Drivers\GesetzeImInternet',
            # (2) 'dejure.org'
            'dejure' => '\S1SYPHOS\Gesetze\Drivers\DejureOnline',
            # (3) 'buzer.de'
            'buzer' => '\S1SYPHOS\Gesetze\Drivers\Buzer',
            # (4) 'lexparency.de'
            'lexparency' => '\S1SYPHOS\Gesetze\Drivers\Lexparency',
        ];

        # If string was passed as order ..
        if (is_string($order)) {
            # .. make it an array
            $order = [$order];
        }

        # Iterate over available drivers ..
        foreach (array_keys($drivers) as $driver) {
            # .. but skip default one(s)
            if (in_array($driver, $order)) {
                continue;
            }

            # Add to order
            $order[] = $driver;
        }

        # Initialize drivers
        foreach ($order as $driver) {
            if (in_array($driver, array_keys($drivers))) {
                $this->drivers[$driver] = new $drivers[$driver]();
            }
        }
    }


    /**
     * Methods
     */

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
            throw new \Exception('Input contains invalid character.');
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


    /**
     * Analyzes a single legal norm
     *
     * @param string $string Legal norm
     * @return array Formatted regex match
     */
    public static function analyze(string $string): array
    {
        if (preg_match(self::$pattern, $string, $matches)) {
            return array_combine(self::$groups, array_slice($matches, 1));
        }

        return [];
    }


    /**
     * Validates a single legal norm (across all providers)
     *
     * @param array $array Formatted regex match
     * @return bool Validity of legal norm
     */
    public function validate(array $array): bool
    {
        # Fail early when regex match is empty
        if (empty($array)) {
            return false;
        }

        # Set default
        $validity = false;

        # Iterate over drivers ..
        foreach ($this->drivers as $driver => $object) {
            # (1) .. skipping blocked drivers
            if (in_array($driver, $this->blockList)) {
                continue;
            }

            # (2) .. validating legal norm
            if ($object->validate($array)) {
                # Upon first hit ..
                # (1) .. affirm its validity
                $validity = true;

                # (2) .. abort the loop
                break;
            }
        }

        return $validity;
    }


    /**
     * Extracts legal norms from text
     *
     * @param string $string Text
     * @return array Formatted regex matches
     */
    public static function extract(string $string): array
    {
        # Look for legal norms in text
        if (preg_match_all(self::$pattern, $string, $matches)) {
            # Create data array
            $data = [];

            foreach ($matches[0] as $index => $match) {
                $array = ['match' => $match];

                foreach (array_slice($matches, 1) as $i => $results) {
                    $array[self::$groups[$i]] = $results[$index];
                }

                $data[] = $array;
            }

            return $data;
        }

        return [];
    }


    /**
     * Transforms legal references into HTML link tags
     *
     * @param string $string Unprocessed text
     * @return string Processed text
     */
    public function linkify(string $string): string
    {
        # Extract matching legal norms
        $matches = self::extract($string);

        # If none were found ..
        if (empty($matches)) {
            # .. return original text
            return $string;
        }

        # Iterate over matches ..
        foreach ($matches as $match) {
            # .. and drivers for each match ..
            foreach ($this->drivers as $driver => $object) {
                # (1) .. skipping blocked drivers
                if (in_array($driver, $this->blockList)) {
                    continue;
                }

                # (2).. blocking invalid laws & legal norms
                if (!$object->validate($match)) {
                    continue;
                }

                # Build `a` tag attributes
                # (1) Set defaults
                $attributes = $this->attributes;

                # (2) Determine `href` attribute
                $attributes['href'] = $object->buildURL($match);

                # (3) Determine `title` attribute
                $attributes['title'] = $object->buildTitle($match, $this->title);

                # (4) Provide fallback for `target` attribute
                if (!isset($attributes['target'])) {
                    $attributes['target'] = '_blank';
                }

                # Abort the loop
                break;
            }

            if (!isset($attributes['href'])) {
                continue;
            }

            # Build `a` tag
            # (1) Format key-value pairs
            $attributes = array_map(function($key, $value) {
                return sprintf('%s="%s"', $key, $value);
            }, array_keys($attributes), array_values($attributes));

            # (2) Combine everything
            $link = '<a ' . implode(' ', $attributes) . '>' . $match['match'] . '</a>';

            # Replace matched legal norm with its `a` tag
            $string = str_replace($match['match'], $link, $string);
        }

        return $string;
    }
}

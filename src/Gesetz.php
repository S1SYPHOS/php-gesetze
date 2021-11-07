<?php

/**
 * Linking german legal norms, dependency-free & GDPR-friendly.
 *
 * @link https://github.com/S1SYPHOS/php-gesetze
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 0.3.0
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
    public $drivers = [
        'gesetze' => null,
        'dejure'  => null,
    ];


    /**
     * The regex, holding the world together in its inmost folds
     *
     * For reference:
     *
     * '/(?:§+|Art\.?|Artikel)\s*(\d+(?:\w\b)?)\s*(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*(?:(?:S\.|Satz)\s*(\d+))?\s*(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*(?:(?:lit\.|litera|Buchst\.|Buchstabe)\s*([a-z]?))?.{0,10}?(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?)/'
     */
    public static $pattern = ''
        # Start
        . '/'
        # Section sign
        . '(?:§+|Art\.?|Artikel)\s*'
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
        . '(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?)'
        # End
        . '/';


    /**
     * Names for capturing groups
     *
     * @var array
     */
    public static $groups = [
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
     * Defines whether laws & legal norms should be validated upon extracting / linking
     *
     * @var bool
     */
    public $validate = true;


    /**
     * Constructor
     *
     * @param string $driver Provider identifier
     * @return void
     * @throws \Exception
     */
    public function __construct(string $driver = 'gesetze')
    {
        if (!array_key_exists($driver, $this->drivers)) {
            throw new \Exception(sprintf('Invalid driver: "%s"', $driver));
        }

        # Move preferred driver to the beginning (if necessary)
        if ($driver !== array_keys($this->drivers)[0]) {
            # (1) Remove preference
            unset($this->drivers[$driver]);

            # (2) Readd at beginning
            $this->drivers = array_merge([$driver => null], $this->drivers);
        }

        # Iterate over available drivers ..
        array_walk($this->drivers, function(&$object, $driver) {
            # .. initializing each one of them
            # (1) 'gesetze-im-internet.de'
            if ($driver === 'gesetze') {
                $object = new \S1SYPHOS\Gesetze\Drivers\GesetzeImInternet();

            }

            # (2) 'dejure.org'
            if ($driver === 'dejure') {
                $object = new \S1SYPHOS\Gesetze\Drivers\DejureOnline();
            }
        });
    }


    /**
     * Methods
     */

    /**
     * Converts roman numerals to arabic numerals
     *
     * @param string $string
     * @return string
     */
    public static function roman2arabic(string $string): string
    {
        if (!preg_match('/[IVX]+/', $string)) {
            return $string;
        }

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

        return (string) $result;
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
        foreach ($this->drivers as $object) {
            # .. validating legal norm
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
    public function extract(string $string, bool $roman2arabic = false): array
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

                # Block invalid laws & legal norms (if enabled)
                if ($this->validate && !$this->validate($array)) {
                    continue;
                }

                # Convert roman to arabic numerals (if enabled)
                if ($roman2arabic) {
                    $array['absatz'] = self::roman2arabic($array['absatz']);
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
        $matches = $this->extract($string);

        # If none were found ..
        if (empty($matches)) {
            # .. return original text
            return $string;
        }

        # Iterate over matches
        foreach ($matches as $match) {
            foreach ($this->drivers as $object) {
                # Block invalid laws & legal norms (if enabled)
                if ($this->validate && !$object->validate($match)) {
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

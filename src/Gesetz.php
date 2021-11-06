<?php

/**
 * Linking german legal norms, no fuss.
 *
 * @link https://github.com/S1SYPHOS/php-gesetze
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 0.2.0
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

            $object = new \S1SYPHOS\Gesetze\Drivers\GesetzeImInternet;

            foreach ($matches[0] as $index => $match) {
                $array = [];

                foreach (array_slice($matches, 1) as $i => $results) {
                    $array[self::$groups[$i]] = $results[$index];
                }

                # Block invalid laws & legal norms (if enabled)
                if ($this->validate && !$object->validate($array)) {
                    continue;
                }

                if ($roman2arabic) {
                    $array['absatz'] = self::roman2arabic($array['absatz']);
                }

                $data[] = [
                    'full' => $match,
                    'meta' => $array,
                ];
            }

            return $data;
        }

        return [];
    }


    /**
     * Links legal norms of a text to 'gesetze-im-internet.de'
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

        $object = new \S1SYPHOS\Gesetze\Drivers\GesetzeImInternet;

        # Iterate over matches
        foreach ($matches as $match) {
            # Block invalid laws & legal norms (if enabled)
            if ($this->validate && !$object->validate($match['meta'])) {
                continue;
            }

            # Build `a` tag attributes
            # (1) Set defaults
            $attributes = $this->attributes;

            # (2) Determine `href` attribute
            $attributes['href'] = $object->buildURL($match['meta']);

            # (3) Determine `title` attribute
            $attributes['title'] = $object->buildTitle($match['meta'], $this->title);

            # (4) Provide fallback for `target` attribute
            if (!isset($attributes['target'])) {
                $attributes['target'] = '_blank';
            }

            # Build `a` tag
            # (1) Format key-value pairs
            $attributes = array_map(function($key, $value) {
                return sprintf('%s="%s"', $key, $value);
            }, array_keys($attributes), array_values($attributes));

            # (2) Combine everything
            $link = '<a ' . implode(' ', $attributes) . '>' . $match['full'] . '</a>';

            # Replace matched legal norm with its `a` tag
            $string = str_replace($match['full'], $link, $string);
        }

        return $string;
    }
}

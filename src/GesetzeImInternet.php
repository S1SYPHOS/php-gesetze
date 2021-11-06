<?php
/**
 * php-gesetze - Linking texts with gesetze-im-internet, no fuss.
 *
 * @link https://github.com/S1SYPHOS/php-gesetze
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 0.2.0
 */

namespace S1SYPHOS;


/**
 * Class GesetzeImInternet
 *
 * Adds links to gesetze-im-internet.de
 *
 * @package php-gesetze
 */
class GesetzeImInternet
{
    /**
     * Properties
     */

    /**
     * Available laws
     *
     * @var array
     */
    public $library;


    /**
     * The regex, holding the world together in its inmost folds
     *
     * For reference:
     *
     * '/(?:§+|Art\.?|Artikel)\s*(\d+(?:\w\b)?)\s*(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*(?:(?:S\.|Satz)\s*(\d+))?\s*(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*(?:(?:lit\.|litera)\s*([a-z]?))?.{0,10}?(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?)/'
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
     * Constructor
     *
     * @param string $file Path to data file
     * @return void
     */
    public function __construct(string $file = null)
    {
        # Determine library file
        $file = $file ?? __DIR__ . '/../laws/data.json';

        # Load law library data
        $this->library = json_decode(file_get_contents($file), true);
    }


    /**
     * Methods
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


    public static function analyze(string $string): array
    {
        if (preg_match(self::$pattern, $string, $matches)) {
            return array_combine(self::$groups, array_slice($matches, 1));
        }

        return [];
    }


    public static function extract(string $text, bool $roman2arabic = false): array
    {
        # Look for legal norms in text
        if (preg_match_all(self::$pattern, $text, $matches)) {
            # Create data array
            $data = [];

            foreach ($matches[0] as $index => $match) {
                $array = [];

                foreach (array_slice($matches, 1) as $i => $results) {
                    $array[self::$groups[$i]] = $results[$index];
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


    public function linkify(string $text): string
    {
        # Extract matching legal norms
        $matches = self::extract($text);

        # If none were found ..
        if (empty($matches)) {
            # .. return original text
            return $text;
        }

        # Iterate over matches
        foreach ($matches as $match) {
            # Get lowercase identifier for current law
            $identifier = strtolower($match['meta']['gesetz']);

            # Check whether current law exists in library ..
            if (in_array($identifier, array_keys($this->library)) === false) {
                # .. otherwise proceed to next match
                continue;
            }

            # Create `a` tag from matched legal norm
            $link = $this->buildLink($identifier, $match);

            # If they are the same though ..
            if ($link === $match) {
                # .. proceed to next match
                continue;
            }

            # Replace matched legal norm with its `a` tag
            $text = str_replace($match['full'], $link, $text);
        }

        return $text;
    }


    protected function buildLink(string $identifier, array $match): string
    {
        # Get data about current law
        $law = $this->library[$identifier];

        # Since `norm` is always a string ..
        $norm = $match['meta']['norm'];

        # .. but PHP decodes JSON numeric keys as integer ..
        if (preg_match('/\b\d+\b/', $norm)) {
            # .. convert them first
            $norm = (int)$norm;
        }

        # Fail early for invalid norms
        if (in_array($norm, array_map('strval', array_keys($law['headings']))) === false) {
            return $match['full'];
        }

        # Set defaults
        $attributes = $this->attributes;

        # Build `href` attribute
        # (1) Set base URL
        $url = 'https://www.gesetze-im-internet.de';

        # (2) Set default HTML file
        $file = '__' . $match['meta']['norm'] . '.html';

        # (3) Except for the 'Grundgesetz' ..
        if ($identifier === 'gg') {
            # .. which is different
            $file = 'art_' . $match['meta']['norm'] . '.html';
        }

        # (4) Combine everything
        $attributes['href'] = sprintf('%s/%s/%s', $url, $law['slug'], $file);

        # Build `title` attribute
        if ($this->title === 'light') {
            $attributes['title'] = $law['law'];
        }

        if ($this->title === 'normal') {
            $attributes['title'] = $law['title'];
        }

        if ($this->title === 'full') {
            $attributes['title'] = $law['headings'][$norm];
        }

        # Provide fallback for `target` attribute
        if (!isset($attributes['target'])) {
            $attributes['target'] = '_blank';
        }

        # Build `a` tag
        # (1) Format key-value pairs
        $attributes = array_map(function($key, $value) {
            return sprintf('%s="%s"', $key, $value);
        }, array_keys($attributes), array_values($attributes));

        # (2) Party time!
        return '<a ' . implode(' ', $attributes) . '>' . $match['full'] . '</a>';
    }
}

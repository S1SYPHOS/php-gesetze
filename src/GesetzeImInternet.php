<?php
/**
 * php-gesetze - Linking texts with gesetze-im-internet, no fuss.
 *
 * @link https://github.com/S1SYPHOS/php-gesetze
 * @license https://www.gnu.org/licenses/gpl-3.0.txt GPL v3
 * @version 0.1.0
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
     * '/(§+|Art|Artikel)\.?\s*(?<norm>\d+(?:\w\b)?)\s*(?:(?:Abs\.\s*)?(?<absatz>\d+|[XIV]+(?:\w\b)?))?\s*(?:S\.\s*(?<satz>\d+))?\s*(?:Nr\.\s*(?<nr>\d+(?:\w\b)?))?\s*(?:lit\.\s*(?<lit>[a-z]?))?.{0,10}?(?<gesetz>\b[A-Z][A-Za-z]*[A-Z](?:(?<buch>(?:\s|\b)[XIV]+)?))/i'
     */
    public static $pattern = ''
        . '/'
        . '(§+|Art|Artikel)\.?\s*'                               # section sign
        . '(?<norm>\d+(?:\w\b)?)\s*'                             # section ('Norm')
        . '(?:(?:Abs\.\s*)?(?<absatz>\d+|[XIV]+(?:\w\b)?))?\s*'  # subsection ('Absatz')
        . '(?:S\.\s*(?<satz>\d+))?\s*'                           # sentence ('Satz')
        . '(?:Nr\.\s*(?<nr>\d+(?:\w\b)?))?\s*'                   # number ('Nummer')
        . '(?:lit\.\s*(?<lit>[a-z]?))?'                          # letter ('Litera')
        . '.{0,10}?'                                             # character limit
        . '(?<gesetz>\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?)' # law ('Gesetz')
        . '/i';


    /**
     * Controls `title` attribute
     *
     * Possible values:
     *
     * 'light'  => abbreviated law (eg 'GG')
     * 'normal' => complete law (eg 'Grundgesetz')
     * 'full'   => official heading (eg '§ 433 Vertragstypische Pflichten beim Kaufvertrag')
     *
     * @var mixed string|false
     */
    public $title = false;


    /**
     * Controls `class` attribute
     *
     * @var string
     */
    public $class = '';


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
        if (!preg_match('/[IVX]+/i', $string)) {
            return $string;
        }

        # Ensure uppercase
        $string = strtoupper($string);

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


    public static function extract(string $text, bool $roman2arabic = true): array
    {
        # TODO: Replace named capturing groups:
        # - `norm`
        # - `absatz`
        # - `satz`
        # - `nr`
        # - `lit`
        # - `gesetz`
        # - `buch`
        if (preg_match_all(self::$pattern, $text, $matches)) {
            # Create data array
            $data = [];

            foreach ($matches[0] as $index => $match) {
                $array = [];

                foreach (array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY) as $part => $results) {
                    $array[$part] = $results[$index];
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

        # Build `class` attribute
        $class = '';

        if (!empty($this->class)) {
            $class = 'class="' . $this->class . '"';
        }

        # Build `href` attribute
        # (1) Set default HTML file
        $file = '__' . $match['meta']['norm'] . '.html';

        # (2) Except for the 'Grundgesetz' ..
        if ($identifier === 'gg') {
            # .. which is different
            $file = 'art_' . $match['meta']['norm'] . '.html';
        }

        # (3) Combine everything
        $href = sprintf('href="https://www.gesetze-im-internet.de/%s/%s"', $law['slug'], $file);

        # Build `title` attribute
        # TODO: Add option for extended description
        $title = '';

        if ($this->title === 'light') {
            $title = 'title="' . $law['law'] . '"';
        }

        if ($this->title === 'normal') {
            $title = 'title="' . $law['title'] . '"';
        }

        if ($this->title === 'full') {
            $title = 'title="' . $law['headings'][$norm] . '"';
        }

        # Merge (existing) attributes
        $attributes = array_filter([$class, $href, $title]);

        return '<a ' . implode(' ', $attributes) . '>' . $match['full'] . '</a>';
    }
}

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
     * Controls `title` attribute
     *
     * Possible values:
     *
     * 'min' => short title
     * 'max' => long title
     *
     * @var mixed
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
     */

    public function __construct() {}


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
        if (preg_match_all('/(§+|Art|Artikel)\.?\s*(?<norm>\d+(?:\w\b)?)\s*(?:(?:Abs\.\s*)?(?<absatz>\d+|[XIV]+(?:\w\b)))?\s*(?:S\.\s*(?<satz>\d+))?\s*(?:Nr\.\s*(?<nr>\d+(?:\w\b)?))?\s*(?:lit\.\s*(?<lit>[a-z]?))?.{0,10}?(?<gesetz>\b[A-Z][A-Za-z]*[A-Z](?:(?<buch>(?:\s|\b)[XIV]+)?))/i', $text, $matches)) {
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
                    'match' => $match,
                    'parts' => $array,
                ];
            }

            return $data;
        }

        return [];
    }


    public function linkify(string $text): string
    {
        $data = self::extract($text);

        if (empty($data)) {
            return $text;
        }

        $laws = json_decode(file_get_contents(__DIR__ . '/../laws/data.json'), true);

        foreach ($data as $item) {
            $identifier = strtolower($item['parts']['gesetz']);

            if (in_array($identifier, array_keys($laws)) === true) {
                $replacement = $this->buildLink($identifier, $item, $laws[$identifier]);

                $text = str_replace($item['match'], $replacement, $text);
            }
        }

        return $text;
    }


    protected function buildLink(string $identifier, array $item, array $law): string
    {
        # Build `class` attribute
        $class = '';

        if (!empty($this->class)) {
            $class = 'class="' . $this->class . '"';
        }

        # Build `href` attribute
        # (1) Set default HTML file
        $file = '__' . $item['parts']['norm'] . '.html';

        # (2) Except for the 'Grundgesetz' ..
        if ($identifier === 'gg') {
            # .. which is different
            $file = 'art_' . $item['parts']['norm'] . '.html';
        }

        # (3) Combine everything
        $href = sprintf('href="https://www.gesetze-im-internet.de/%s/%s"', $law['slug'], $file);

        # Build `title` attribute
        # TODO: Add option for extended description
        $title = '';

        if ($this->title === 'min') {
            $title = 'title="' . $law['law'] . '"';
        }

        if ($this->title === 'max') {
            $title = 'title="' . $law['title'] . '"';
        }

        # Merge (existing) attributes
        $attributes = array_filter([$class, $href, $title]);

        return '<a ' . implode(' ', $attributes) . '>' . $item['match'] . '</a>';
    }
}

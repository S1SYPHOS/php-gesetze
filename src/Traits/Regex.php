<?php

namespace S1SYPHOS\Gesetze\Traits;


trait Regex
{
    /**
     * Properties
     */

    /**
     * The regex, holding the world together in its inmost folds
     *
     * For reference:
     *
     * '/(?:§+|Art\.?|Artikel)\s*(\d+(?:\w\b)?)\s*(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*(?:(?:S\.|Satz)\s*(\d+))?\s*(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*(?:(?:lit\.|litera|Buchst\.|Buchstabe)\s*([a-z]?))?.{0,10}?(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?\b)/'
     *
     * @var string
     */
    public $pattern = ''
        # Start
        . '/'
        # Section sign
        . '(?:§+|&sect;|Art\.?|Artikel)\s*'
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
    public $groups = [
        'norm',
        'absatz',
        'satz',
        'nr',
        'lit',
        'gesetz',
    ];


    /**
     * Methods
     */

    /**
     * Creates match array
     *
     * @param array $match Matched legal norm
     * @return array Formatted regex match
     */
    private function groupMatch(array $match): array
    {
        return array_combine($this->groups, array_slice($match, 1));
    }


    /**
     * Analyzes a single legal norm
     *
     * @param string $string Legal norm
     * @return array Formatted regex match
     */
    public function analyze(string $string): array
    {
        if (preg_match($this->pattern, $string, $matches)) {
            return array_filter($this->groupMatch($matches));
        }

        return [];
    }
}

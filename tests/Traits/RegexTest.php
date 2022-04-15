<?php

namespace S1SYPHOS\Gesetze\Tests\Traits;

use S1SYPHOS\Gesetze\Traits\Regex;


/**
 * Class Regex
 *
 * Adds tests for trait 'Regex'
 */
class RegexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Setup
     */

    use Regex;


    /**
     * Tests
     */

    public function testAnalyze(): void
    {
        # Setup
        # (1) Norms
        $norms = [
            # Section sign
            '§ 1 BGB' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],
            '§§ 1 BGB' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],
            '<span>&sect; 1 BGB</span>' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],
            'Artikel 1 BGB' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],
            'Art. 1 BGB' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],

            # Section
            '§ 1 BGB' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],
            '§ 1a BGB' => [
                'norm'   => '1a',
                'gesetz' => 'BGB',
            ],

            # Subsection
            '§ 1 Absatz 2 BGB' => [
                'norm'   => '1',
                'absatz' => '2',
                'gesetz' => 'BGB',
            ],
            '§ 1 Abs. 2 BGB' => [
                'norm'   => '1',
                'absatz' => '2',
                'gesetz' => 'BGB',
            ],
            '§ 1 II BGB' => [
                'norm'   => '1',
                'absatz' => 'II',
                'gesetz' => 'BGB',
            ],
            '§ 1 Absatz 2a BGB' => [
                'norm'   => '1',
                'absatz' => '2a',
                'gesetz' => 'BGB',
            ],
            '§ 1 Abs. 2a BGB' => [
                'norm'   => '1',
                'absatz' => '2a',
                'gesetz' => 'BGB',
            ],
            '§ 1 IIa BGB' => [
                'norm'   => '1',
                'absatz' => 'IIa',
                'gesetz' => 'BGB',
            ],


            # Sentence
            '§ 1 Satz 1 BGB' => [
                'norm'   => '1',
                'satz'   => '1',
                'gesetz' => 'BGB',
            ],
            '§ 1 S. 1 BGB' => [
                'norm'   => '1',
                'satz'   => '1',
                'gesetz' => 'BGB',
            ],

            # Number
            '§ 1 Nummer 1 BGB' => [
                'norm'   => '1',
                'nr'     => '1',
                'gesetz' => 'BGB',
            ],
            '§ 1 Nr. 1 BGB' => [
                'norm'   => '1',
                'nr'     => '1',
                'gesetz' => 'BGB',
            ],

            # Letter
            '§ 1 litera a BGB' => [
                'norm'   => '1',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],
            '§ 1 lit. a BGB' => [
                'norm'   => '1',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],
            '§ 1 Buchstabe a BGB' => [
                'norm'   => '1',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],
            '§ 1 Buchst. a BGB' => [
                'norm'   => '1',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],

            # Law
            '§ 1 BGB' => [
                'norm'   => '1',
                'gesetz' => 'BGB',
            ],
            '§ 1 SGB V' => [
                'norm'   => '1',
                'gesetz' => 'SGB V',
            ],
        ];

        # Run function
        foreach ($norms as $full => $meta) {
            # Assert result
            $this->assertEquals($this->analyze($full), $meta);
        }
    }


    public function testAnalyzeEmpty(): void
    {
        # Setup
        # (1) Norms
        $norms = [
            '',
            '§ 1 by itself == useless',
            'This is for educational purposes only',
        ];

        # Run function
        foreach ($norms as $norm) {
            # Assert result
            $this->assertEquals($this->analyze($norm), []);
        }
    }
}

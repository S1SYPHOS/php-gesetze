<?php

namespace S1SYPHOS\Gesetze\Tests;


/**
 * Class GesetzTest
 *
 * Adds tests for class `Gesetz`
 */
class GesetzTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests
     */

    public function testValidDriver(): void
    {
        # Setup
        # (1) Providers
        $drivers = [
            'gesetze' => '\S1SYPHOS\Gesetze\Drivers\GesetzeImInternet',
            'dejure' => '\S1SYPHOS\Gesetze\Drivers\DejureOnline',
        ];

        foreach ($drivers as $driver => $className) {
            # Run function
            $result = new \S1SYPHOS\Gesetze\Gesetz($driver);

            # Assert result
            $this->assertInstanceOf('\S1SYPHOS\Gesetze\Gesetz', $result);

            foreach ($result->drivers as $driver => $object) {
                $this->assertInstanceOf($drivers[$driver], $object);
            }
        }
    }


    public function testInvalidDriver(): void
    {
        # Setup
        # (1) Providers
        $drivers = [
            '',
            '?!#@=',
            'g3s3tz3',
            'd3!ur3',
        ];

        # Assert exception
        $this->expectException(\Exception::class);

        foreach ($drivers as $driver) {
            # Run function
            $result = new \S1SYPHOS\Gesetze\Gesetz($driver);
        }
    }


    public function testAnalyze(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) Norms
        $norms = [
            # Section
            '§ 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1a BGB' => [
                'norm'   => '1a',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§§ 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§§ 1a BGB' => [
                'norm'   => '1a',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            'Artikel 12 GG' => [
                'norm'   => '12',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'GG',
            ],
            'Artikel 12a GG' => [
                'norm'   => '12a',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'GG',
            ],
            'Art. 12 GG' => [
                'norm'   => '12',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'GG',
            ],
            'Art. 12a GG' => [
                'norm'   => '12a',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'GG',
            ],

            # Subsection
            '§ 1 Absatz 2 BGB' => [
                'norm'   => '1',
                'absatz' => '2',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 Abs. 2 BGB' => [
                'norm'   => '1',
                'absatz' => '2',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 II BGB' => [
                'norm'   => '1',
                'absatz' => 'II',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],

            '§ 1 Absatz 2a BGB' => [
                'norm'   => '1',
                'absatz' => '2a',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 Abs. 2a BGB' => [
                'norm'   => '1',
                'absatz' => '2a',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 IIa BGB' => [
                'norm'   => '1',
                'absatz' => 'IIa',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],


            # Sentence
            '§ 1 Satz 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '1',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 S. 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '1',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],

            # Number
            '§ 1 Nummer 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '1',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 Nr. 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '1',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],

            # Letter
            '§ 1 litera a BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],
            '§ 1 lit. a BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],
            '§ 1 Buchstabe a BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],
            '§ 1 Buchst. a BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => 'a',
                'gesetz' => 'BGB',
            ],

            # Law
            '§ 1 BGB' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
            '§ 1 SGB V' => [
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'SGB V',
            ],
        ];

        # Run function
        foreach ($norms as $full => $meta) {
            # Assert result
            $this->assertEquals($meta, $object::analyze($full));
        }
    }


    public function testExtract(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) Norms
        $norms = [
            # Note: Despite using a section sign instead of 'Art(ikel)', this works!
            '§ 1 GG'   => [
                'match'  => '§ 1 GG',
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'GG',
            ],
            '§ 1 BGB' => [
                'match'  => '§ 1 BGB',
                'norm'   => '1',
                'absatz' => '',
                'satz'   => '',
                'nr'     => '',
                'lit'    => '',
                'gesetz' => 'BGB',
            ],
        ];

        # Run function
        foreach ($norms as $match => $data) {
            # Assert result
            $this->assertEquals([$data], $object->extract($match));
        }
    }


    public function testInvalidExtract(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) Norms
        $norms = [
            '§ 1a BGB' => [],
            '§ 1 GGGG' => [],
        ];

        # Run function
        foreach ($norms as $norm => $expected) {
            # Assert result
            $this->assertEquals($expected, $object->extract($norm));
        }
    }


    public function testLinkify(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) Text
        # Enforce UTF-8 encoding
        $text = '<!DOCTYPE html><meta charset="UTF-8">';

        # Insert test string
        $text .= '<div>';
        $text .= 'This is a <strong>simple</strong> HTML text.';
        $text .= 'It contains legal norms, like Art. 12 Abs. 1 GG ..';
        $text .= '.. or § 433 II BGB!';
        $text .= 'At the same time, there are invalid ones, like ..';
        $text .= '§ 1a BGB and § 1 GGGG';
        $text .= '</div>';

        # (3) HTML document
        $dom = new \DOMDocument;

        # Run function
        @$dom->loadHTML($text);
        $result = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(0, count($result));

        # Run function
        @$dom->loadHTML($object->linkify($text));
        $result = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(2, count($result));

        # Change condition `validate`
        $object->validate = false;

        # Run function
        @$dom->loadHTML($object->linkify($text));
        $result = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(4, count($result));
    }
}
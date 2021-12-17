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
     * Properties
     */

    /**
     * @var string
     */
    private static $text;


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Text
        # (a) Enforce UTF-8 encoding
        $text = '<!DOCTYPE html><meta charset="UTF-8">';

        # (b) Insert test string
        $text .= '<div>';
        $text .= 'This is a <strong>simple</strong> HTML text.';
        $text .= 'It contains legal norms, like Art. 12 Abs. 1 GG ..';
        $text .= '.. or § 433 II BGB!';
        $text .= 'At the same time, there are invalid ones, like ..';
        $text .= '§ 1a BGB and § 1 GGGG ..';
        $text .= '.. and what european law, like Art. 2 Abs. 2 DSGVO?';
        $text .= '</div>';

        self::$text = $text;
    }


    /**
     * Tests
     */

    public function testValidDriver(): void
    {
        # Setup
        # (1) Providers
        $drivers = [
            'gesetze'    => '\S1SYPHOS\Gesetze\Drivers\GesetzeImInternet',
            'dejure'     => '\S1SYPHOS\Gesetze\Drivers\DejureOnline',
            'buzer'      => '\S1SYPHOS\Gesetze\Drivers\Buzer',
            'lexparency' => '\S1SYPHOS\Gesetze\Drivers\Lexparency',
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


    public function testDriverOrder(): void
    {
        # Setup
        # (1) Providers
        $drivers = [
            # .. as string
            [
                'actual' => 'gesetze',
                'expected' => ['gesetze', 'dejure', 'buzer', 'lexparency'],
            ],
            [
                'actual' => 'buzer',
                'expected' => ['buzer', 'gesetze', 'dejure', 'lexparency'],
            ],
            [
                'actual' => 'dejure',
                'expected' => ['dejure', 'gesetze', 'buzer', 'lexparency'],
            ],
            [
                'actual' => 'lexparency',
                'expected' => ['lexparency', 'gesetze', 'dejure', 'buzer'],
            ],

            # .. invalid entry
            [
                'actual' => '',
                'expected' => ['gesetze', 'dejure', 'buzer', 'lexparency'],
            ],
            [
                'actual' => '?!#@=',
                'expected' => ['gesetze', 'dejure', 'buzer', 'lexparency'],
            ],

            # .. as list
            [
                'actual' => ['buzer'],
                'expected' => ['buzer', 'gesetze', 'dejure', 'lexparency'],
            ],
            [
                'actual' => ['lexparency'],
                'expected' => ['lexparency', 'gesetze', 'dejure', 'buzer'],
            ],
            [
                'actual' => ['buzer', 'dejure'],
                'expected' => ['buzer', 'dejure', 'gesetze', 'lexparency'],
            ],
            [
                'actual' => ['lexparency', 'buzer'],
                'expected' => ['lexparency', 'buzer', 'gesetze', 'dejure'],
            ],
            [
                'actual' => ['gesetze', 'lexparency', 'buzer'],
                'expected' => ['gesetze', 'lexparency', 'buzer', 'dejure'],
            ],
            [
                'actual' => ['buzer', 'dejure', 'lexparency'],
                'expected' => ['buzer', 'dejure', 'lexparency', 'gesetze'],
            ],
            [
                'actual' => ['lexparency', 'dejure', 'buzer', 'gesetze'],
                'expected' => ['lexparency', 'dejure', 'buzer', 'gesetze'],
            ],
            [
                'actual' => ['dejure', 'buzer', 'lexparency', 'gesetze'],
                'expected' => ['dejure', 'buzer', 'lexparency', 'gesetze'],
            ],

            # .. more than four entries
            [
                'actual' => ['buzer', 'buzer', 'gesetze', 'dejure', 'buzer', 'gesetze'],
                'expected' => ['buzer', 'gesetze', 'dejure', 'lexparency'],
            ],

            # .. invalid entries
            [
                'actual' => ['', '?!#@=', 'g3s3tz3', 'd3!ur3'],
                'expected' => ['gesetze', 'dejure', 'buzer', 'lexparency'],
            ],
        ];

        # Run function #1
        $result1 = new \S1SYPHOS\Gesetze\Gesetz($drivers);

        # Assert result
        $this->assertEquals(array_keys($result1->drivers), ['gesetze', 'dejure', 'buzer', 'lexparency']);

        foreach ($drivers as $item) {
            # Run function #2
            $result2 = new \S1SYPHOS\Gesetze\Gesetz($item['actual']);

            # Assert result
            $this->assertEquals(array_keys($result2->drivers), $item['expected']);
        }
    }


    public function testAnalyze(): void
    {
        # Setup
        # (1) Norms
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
            $this->assertEquals($meta, \S1SYPHOS\Gesetze\Gesetz::analyze($full));
        }
    }


    public function testLinkify(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # Run function
        @$dom->loadHTML(self::$text);
        $result1 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(0, count($result1));

        # Run function
        @$dom->loadHTML($object->linkify(self::$text));
        $result2 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(3, count($result2));

        # Change condition `blockList`
        $object->blockList = [
            'gesetze',
            'dejure',
            'buzer',
            'lexparency',
        ];

        @$dom->loadHTML($object->linkify(self::$text));
        $result3 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(0, count($result3));

        # Disable 'DSGVO' detection
        $object->blockList = [
            'dejure',
            'lexparency',
        ];

        # Run function
        @$dom->loadHTML($object->linkify(self::$text));
        $result4 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(2, count($result4));
    }


    public function testLinkifyTitle()
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) `Title` attributes
        $titles = [
            'Art. 12 Abs. 1 GG' => [
                'light' => 'GG',
                'normal' => 'Grundgesetz für die Bundesrepublik Deutschland',
                'full' => 'Art 12',
            ],
            '§ 433 II BGB' => [
                'light' => 'BGB',
                'normal' => 'Bürgerliches Gesetzbuch',
                'full' => '§ 433 Vertragstypische Pflichten beim Kaufvertrag',
            ],
            'Art. 2 Abs. 2 DSGVO' => [
                'light' => 'DSGVO',
                'normal' => 'Verordnung (EU) 2016/679 des Europäischen Parlaments und des Rates vom 27. April 2016 zum Schutz natürlicher Personen bei der Verarbeitung personenbezogener Daten, zum freien Datenverkehr und zur Aufhebung der Richtlinie 95/46/EG',
                'full' => 'Art.  2 Sachlicher Anwendungsbereich',
            ],
        ];


        # Run function #1
        @$dom->loadHTML($object->linkify(self::$text));
        $links1 = $dom->getElementsByTagName('a');

        foreach ($links1 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), '');
        }

        # Change condition `title`
        $object->title = 'light';

        # Run function #2
        @$dom->loadHTML($object->linkify(self::$text));
        $links2 = $dom->getElementsByTagName('a');

        foreach ($links2 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), $titles[$link->nodeValue]['light']);
        }

        # Change condition `title`
        $object->title = 'normal';

        # Run function #3
        @$dom->loadHTML($object->linkify(self::$text));
        $links3 = $dom->getElementsByTagName('a');

        foreach ($links3 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), $titles[$link->nodeValue]['normal']);
        }

        # Change condition `title`
        $object->title = 'full';

        # Run function #4
        @$dom->loadHTML($object->linkify(self::$text));
        $links4 = $dom->getElementsByTagName('a');

        foreach ($links4 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), $titles[$link->nodeValue]['full']);
        }
    }


    public function testLinkifyAttributes()
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) Attributes
        $attributes = [
            'attr1' => 'some-value',
            'attr2' => 'other-value',
        ];

        # Run function #1
        @$dom->loadHTML($object->linkify(self::$text));
        $links1 = $dom->getElementsByTagName('a');

        foreach ($links1 as $link) {
            # Assert result
            foreach ($attributes as $attribute => $value) {
                $this->assertEquals($link->getAttribute($attribute), '');
            }
        }

        # Change condition `attributes`
        $object->attributes = $attributes;

        # Run function #2
        @$dom->loadHTML($object->linkify(self::$text));
        $links2 = $dom->getElementsByTagName('a');

        foreach ($links2 as $link) {
            # Assert result
            foreach ($attributes as $attribute => $value) {
                $this->assertEquals($link->getAttribute($attribute), $value);
            }
        }
    }


    public function testRoman2ArabicInvalid()
    {
        # Setup
        # (1) Roman numerals
        $invalidRomans = [
            '',
            'Y',
            'AZ',
            'OMG',
            'LOL',
            'ROFL',
        ];

        # Assert exception
        $this->expectException(\Exception::class);

        # Run function
        foreach ($invalidRomans as $roman) {
            # Run function
            \S1SYPHOS\Gesetze\Gesetz::roman2arabic($roman);
        }
    }


    public function testRoman2Arabic()
    {
        # Setup
        # (1) Roman numerals
        $romans = [
            'II' => 2,
            'IV' => 4,
            'VI' => 6,
            'IX' => 9,
            'XIV' => 14,
            'XIX' => 19,
        ];

        # Run function
        foreach ($romans as $roman => $expected) {
            # Assert ..
            # (1) result for uppercase
            $this->assertEquals(\S1SYPHOS\Gesetze\Gesetz::roman2arabic($roman), $expected);

            # (2) result for lowercase
            $this->assertEquals(\S1SYPHOS\Gesetze\Gesetz::roman2arabic(strtolower($roman)), $expected);
        }
    }
}

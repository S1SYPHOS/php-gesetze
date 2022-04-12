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
     * Text (with legal references)
     *
     * @var string
     */
    private static $text;


    /**
     * Text (without legal references)
     *
     * @var string
     */
    private static $emptyText;


    /**
     * Setup
     */

    public static function setUpBeforeClass(): void
    {
        # Setup
        # (1) Text (with legal references)
        # (a) Enforce UTF-8 encoding
        $text = '<!DOCTYPE html><meta charset="UTF-8">';

        # (b) Insert test string
        $text .= '<div>';
        $text .= 'This is a <strong>simple</strong> HTML text.';
        $text .= 'It contains legal norms, like Art. 12 Abs. 1 GG ..';
        $text .= '<span class="§ 1">&sect; 1 ZPO</span> was there even before them!' . "\n";
        $text .= '.. or § 433 II BGB! It also refers to § 1 of some legal document.';
        $text .= 'At the same time, there are invalid ones, like ..';
        $text .= '§ 1a BGB and § 1 GGGG ..';
        $text .= '.. and what european law, like Art. 2 Abs. 2 DSGVO?';
        $text .= '</div>';

        self::$text = $text;

        # (2) Text (without legal references)
        # (a) Enforce UTF-8 encoding
        $text = '<!DOCTYPE html><meta charset="UTF-8">';

        # (b) Insert test string
        $text .= '<div>';
        $text .= 'This text comes without legal references,' . "\n";
        $text .= 'because § 1 by itself does not mean anything!';
        $text .= '</div>';

        self::$emptyText = $text;
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
            $this->assertEquals($meta, \S1SYPHOS\Gesetze\Gesetz::analyze($full));
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
            $this->assertEquals([], \S1SYPHOS\Gesetze\Gesetz::analyze($norm));
        }
    }


    public function testExtract(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) Extracted legal norms
        $expected = [
            'Art. 12 Abs. 1 GG',
            '&sect; 1 ZPO',
            '§ 433 II BGB',
            '§ 1a BGB',
            '§ 1 GGGG',
            'Art. 2 Abs. 2 DSGVO',
        ];

        # Run function
        $result = $object->extract(self::$text);

        # Assert result
        $this->assertEquals($result, $expected);
    }


    public function testExtractEmpty(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # Run function
        $result = $object->extract(self::$emptyText);

        # Assert result
        $this->assertEquals($result, []);
    }


    public function testGesetzify(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # Run function #1
        @$dom->loadHTML(self::$text);
        $result1 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(0, count($result1));

        # Run function #2
        @$dom->loadHTML($object->gesetzify(self::$text));
        $result2 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(4, count($result2));
    }


    public function testGesetzifyEmpty(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # Run function #
        $result = $object->gesetzify(self::$emptyText);

        # Assert result
        $this->assertEquals($result, self::$emptyText);
    }


    public function testGesetzifyCallback(): void
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) Callback function
        $callback = function(array $match): string
        {
            return sprintf('<strong>%s</strong>', $match[0]);
        };

        # Run function #1
        @$dom->loadHTML(self::$text);
        $result1 = $dom->getElementsByTagName('strong');

        # Assert result
        $this->assertEquals(1, count($result1));

        # Run function #2
        @$dom->loadHTML($object->gesetzify(self::$text, $callback));
        $result2 = $dom->getElementsByTagName('strong');

        # Assert result
        $this->assertEquals(7, count($result2));
    }


    public function testGesetzifyBlockList(): void
    {    # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # Change condition `blockList`
        $object->blockList = [
            'gesetze',
            'dejure',
            'buzer',
            'lexparency',
        ];

        # Run function #1
        @$dom->loadHTML($object->gesetzify(self::$text));
        $result1 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(0, count($result1));

        # Disable 'DSGVO' detection
        $object->blockList = [
            'dejure',
            'lexparency',
        ];

        # Run function #2
        @$dom->loadHTML($object->gesetzify(self::$text));
        $result2 = $dom->getElementsByTagName('a');

        # Assert result
        $this->assertEquals(3, count($result2));
    }


    public function testGesetzifyTitle()
    {
        # Setup
        # (1) Instance
        $object = new \S1SYPHOS\Gesetze\Gesetz();

        # (2) HTML document
        $dom = new \DOMDocument;

        # (3) `Title` attributes
        $titles = [
            'Art. 12 Abs. 1 GG' => [
                'light'  => 'GG',
                'normal' => 'Grundgesetz für die Bundesrepublik Deutschland',
                'full'   => 'Art 12',
            ],
            '§ 1 ZPO' => [
                'light'  => 'ZPO',
                'normal' => 'Zivilprozessordnung',
                'full'   => '§ 1 Sachliche Zuständigkeit',
            ],
            '§ 433 II BGB' => [
                'light'  => 'BGB',
                'normal' => 'Bürgerliches Gesetzbuch',
                'full'   => '§ 433 Vertragstypische Pflichten beim Kaufvertrag',
            ],
            'Art. 2 Abs. 2 DSGVO' => [
                'light'  => 'DSGVO',
                'normal' => 'Verordnung (EU) 2016/679 des Europäischen Parlaments und des Rates vom 27. April 2016 zum Schutz natürlicher Personen bei der Verarbeitung personenbezogener Daten, zum freien Datenverkehr und zur Aufhebung der Richtlinie 95/46/EG',
                'full'   => 'Art.  2 Sachlicher Anwendungsbereich',
            ],
        ];


        # Run function #1
        @$dom->loadHTML($object->gesetzify(self::$text));
        $links1 = $dom->getElementsByTagName('a');

        foreach ($links1 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), '');
        }

        # Change condition `title`
        $object->title = 'light';

        # Run function #2
        @$dom->loadHTML($object->gesetzify(self::$text));
        $links2 = $dom->getElementsByTagName('a');

        foreach ($links2 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), $titles[$link->nodeValue]['light']);
        }

        # Change condition `title`
        $object->title = 'normal';

        # Run function #3
        @$dom->loadHTML($object->gesetzify(self::$text));
        $links3 = $dom->getElementsByTagName('a');

        foreach ($links3 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), $titles[$link->nodeValue]['normal']);
        }

        # Change condition `title`
        $object->title = 'full';

        # Run function #4
        @$dom->loadHTML($object->gesetzify(self::$text));
        $links4 = $dom->getElementsByTagName('a');

        foreach ($links4 as $link) {
            # Assert result
            $this->assertEquals($link->getAttribute('title'), $titles[$link->nodeValue]['full']);
        }
    }


    public function testGesetzifyAttributes()
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
        @$dom->loadHTML($object->gesetzify(self::$text));
        $links1 = $dom->getElementsByTagName('a');

        foreach ($links1 as $link) {
            # Assert result
            foreach ($attributes as $attribute => $value) {
                $this->assertEquals($link->getAttribute($attribute), '');
            }

            $this->assertEquals($link->getAttribute('target'), '_blank');
        }

        # Change condition `attributes`
        $object->attributes = $attributes;

        # Run function #2
        @$dom->loadHTML($object->gesetzify(self::$text));
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

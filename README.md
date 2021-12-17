# php-gesetze
[![Build](https://ci.codeberg.org/api/badges/S1SYPHOS/php-gesetze/status.svg)](https://codeberg.org/S1SYPHOS/php-gesetze/issues)

Linking german legal norms, dependency-free & GDPR-friendly. `php-gesetze` automatically transforms legal references into `a` tags - batteries included.

There's also a Python port of this library, called [`py-gesetze`](https://codeberg.org/S1SYPHOS/py-gesetze).


## Getting started

Upon invoking the main class, you may specify your preferred provider (or 'driver'), like this:

```php
$object = new \S1SYPHOS\Gesetze\Gesetz('dejure');
```

It's also possible to specify your desired order of two or more drivers as array:

```php
$object = new \S1SYPHOS\Gesetze\Gesetz(['dejure', 'buzer']);
```

**Note:** This option defaults to `gesetze`, which is a good overall choice, simply because of the vast array of supported laws. However, other possible values are `'dejure'`, `'buzer'` and `'lexparency'`.

Out of the box, `php-gesetze` cycles through all known drivers until a match is found. If you want to exclude certain drivers, have a look at the `$object->blockList` option.


## Usage

From there, the following functions are available:


### `analyze(string $string): array`

Analyzes a single legal norm:

```php
$result = \S1SYPHOS\Gesetze\Gesetz::analyze('Art. 1 II GG');

var_dump($result);

# array(6) {
#   ["norm"]=>
#   string(1) "1"
#   ["absatz"]=>
#   string(2) "II"
#   ["satz"]=>
#   string(0) ""
#   ["nr"]=>
#   string(0) ""
#   ["lit"]=>
#   string(0) ""
#   ["gesetz"]=>
#   string(2) "GG"
# }
```


### `validate(array $array): bool`

Validates a single legal norm (across all providers):

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz();

var_dump($obj->validate($obj::analyze('§ 433 II BGB')));

# bool(true)

foreach ($obj->extract('While § 433 II BGB exists, Art. 4c GG does not!') as $match) {
    var_dump($obj->validate($match);
}

# bool(true)
# bool(false)
```

**Note:** In the context of this library, being *valid* means *linkable by at least one provider*, as in *to be found in their database*.


### `roman2arabic(string $string): string`

Converts roman numerals to arabic numerals:

```php
echo \S1SYPHOS\Gesetze\Gesetz::roman2arabic('IX');

# 9
```


### `linkify(string $string): string`

Transforms legal references into HTML link tags:

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz();

echo $obj->linkify('This is a simple text, featuring § 1 I Nr. 1 BGB as well as Art. 4c GG');

# This is a simple text, featuring <a href="https://www.gesetze-im-internet.de/bgb/__1.html" title="§ 1 Beginn der Rechtsfähigkeit">§ 1 I Nr. 1 BGB</a> as well as Art. 4c GG
```


## Example

```php
include_once 'vendor/autoload.php';

# Insert test string
$text  = '<div>';
$text .= 'This is a <b>simple</b> HTML text.';
$text .= 'It contains legal norms, like Art. 12 I GG.';
$text .= '.. or § 433 II nr. 2 BGB!';
$text .= '</div>';

# Initialize object
$obj = new \S1SYPHOS\Gesetze\Gesetz();

# Transform text
echo $obj->linkify($text);

# <div>This is a <b>simple</b> HTML text.
# It contains legal norms, like <a href="https://www.gesetze-im-internet.de/gg/art_12.html" target="_blank">Art. 12 I GG</a>.
# .. or <a href="https://www.gesetze-im-internet.de/bgb/__433.html" target="_blank">§ 433 II nr. 2 BGB</a>!
# </div>
```

**Note:** Caching the result (to avoid repeated lookups & save resources) is beyond the scope of this library and therefore totally up to you!


## Configuration

There are several settings you may use in order to change the behavior of the library:


### `$object->drivers (array)`

Associative array, holding all available drivers (already initialized), where the corresponding keys are `'gesetze'`, `'dejure'`, `'buzer'` & `'lexparency'`.


### `$object->blockList (array)`

Non-associative array, holding driver that should not be used when matching legal norms. Possible values are `'gesetze'`, `'dejure'`, `'buzer'` & `'lexparency'`.


### `$object::$pattern (string)`

The regex responsible for detecting legal norms. For reference, it amounts to this:

```php
'/(?:§+|Art\.?|Artikel)\s*(\d+(?:\w\b)?)\s*(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*(?:(?:S\.|Satz)\s*(\d+))?\s*(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*(?:(?:lit\.|litera|Buchst\.|Buchstabe)\s*([a-z]?))?.{0,10}?(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?)/'
```


### `$object->attributes (array)`

Other HTML attributes to be applied globally:

```php
$object->attributes = [
    'attr1' => 'some-value',
    'attr2' => 'other-value',
];

# .. would generate links like this:

<a href="https://example.com/some-law" attr1="some-value" attr2="other-value">§ 1 SomeLaw</a>
```


### `$object->title (false|string)`

Controls `title` attribute:

| Option     | Description                        |
| ---------- | ---------------------------------- |
| `false`    | No `title` attribute (default)     |
| `'light'`  | abbreviated law (eg 'GG')          |
| `'normal'` | complete law (eg 'Grundgesetz')    |
| `'full'`   | official heading (eg 'Artikel 12') |


### `$object->validate (bool)`

Defines whether laws & legal norms should be validated upon extracting / linking - default to `true`. When `false`, legal norms like `'Art. 1 GGGG'` (invalid law) or `'Art. 12a GG'` (invalid norm) would be seen as valid and therefore be extracted / linked.

**Note:** In this case, it is strongly recommended to avoid setting `$object->title` to `'full'` - you have been warned!


## Credits

This library is based on ..

- .. an adapted (and somewhat improved) version of the [`jura_regex`](https://github.com/kiersch/jura_regex) regex package by Philipp Kiersch (originally written in Python).
- .. an adapted (and somewhat modified) version of the [`gesetze`](https://github.com/matejgrahovac/gesetze) crawler package by Matej Grahovac (originally written in Python).


## Special Thanks

I'd like to thank everybody that's making free & open source software - you people are awesome. Also I'm always thankful for feedback and bug reports :)

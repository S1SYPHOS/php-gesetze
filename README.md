# php-gesetze
[![License](https://badgen.net/badge/license/GPL/blue)](https://codeberg.org/S1SYPHOS/php-gesetze/src/branch/main/LICENSE) [![Packagist](https://badgen.net/packagist/v/s1syphos/php-gesetze)](https://packagist.org/packages/s1syphos/php-gesetze) [![Build](https://ci.codeberg.org/api/badges/S1SYPHOS/php-gesetze/status.svg)](https://codeberg.org/S1SYPHOS/php-gesetze/issues)

Linking german legal norms, dependency-free & GDPR-friendly. `php-gesetze` automatically transforms legal references into `a` tags - batteries included.

There's also a Python port of this library, called [`py-gesetze`](https://codeberg.org/S1SYPHOS/py-gesetze).

For API documentation (powered by [phpDocumentor](https://www.phpdoc.org)), see [here](https://s1syphos.codeberg.page/php-gesetze).


## Installation

It's available for [Composer](https://getcomposer.org):

```text
composer require s1syphos/php-gesetze
```


## Getting started

Upon invoking the main class, you may specify your preferred provider (or 'driver'), like this:

```php
$object = new \S1SYPHOS\Gesetze\Gesetz('dejure');
```

It's also possible to specify selected of two or more drivers as array:

```php
$object = new \S1SYPHOS\Gesetze\Gesetz(['dejure', 'buzer']);
```

**Note:** This defaults to all available drivers, which is a good overall choice, simply because of the vast array of supported laws. Possible values are `gesetze`, `'dejure'`, `'buzer'` and `'lexparency'`.

Out of the box, `php-gesetze` cycles through all known drivers until a match is found.


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


### `validate(string $string): bool`

Validates a single legal norm (across all selected providers:

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz();

var_dump($obj->validate('§ 433 II BGB'));

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


### `extract(string $string): array`

Extracts legal norms as array of strings:

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz();

$result = $obj->extract('This string contains Art. 12 Abs. 1 GG and Art. 2 Abs. 2 DSGVO - for educational purposes only.')

var_dump($result);

# array(2) {
#   [0]=>
#   string(17) "Art. 12 Abs. 1 GG"
#   [1]=>
#   string(19) "Art. 2 Abs. 2 DSGVO"
# }
```


### `gesetzify(string $string, callable $callback): string`

Transforms legal references into HTML link tags:

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz();

echo $obj->gesetzify('This is a simple text, featuring § 1 I Nr. 1 BGB as well as Art. 4c GG');

# This is a simple text, featuring <a href="https://www.gesetze-im-internet.de/bgb/__1.html" title="§ 1 Beginn der Rechtsfähigkeit">§ 1 I Nr. 1 BGB</a> as well as Art. 4c GG
```

**Note:** For more flexibility, you may use your own `callback` method as second parameter of `gesetzify`. Callbacks are being passed arrays representing matched legal norms. This way, you could highlight them using `<strong>` tags instead of converting them into `a` tags. Default: (private) method `linkify`


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
echo $obj->gesetzify($text);

# <div>This is a <b>simple</b> HTML text.
# It contains legal norms, like <a href="https://www.gesetze-im-internet.de/gg/art_12.html" target="_blank">Art. 12 I GG</a>.
# .. or <a href="https://www.gesetze-im-internet.de/bgb/__433.html" target="_blank">§ 433 II nr. 2 BGB</a>!
# </div>
```

**Note:** Caching the result (to avoid repeated lookups & save resources) is beyond the scope of this library and therefore totally up to you!


## Configuration

There are several settings you may use in order to change the behavior of the library:


### `$object->drivers (array)`

Associative array, holding all available drivers (already initialized), where the corresponding keys are `'gesetze'`, `'dejure'`, `'buzer'` & `'lexparency'` (default).


### `$object->pattern (string)`

The regex responsible for detecting legal norms. For reference, it amounts to this:

```php
'/(?:§+|Art\.?|Artikel)\s*(\d+(?:\w\b)?)\s*(?:(?:Abs(?:atz|\.)\s*)?((?:\d+|[XIV]+)(?:\w\b)?))?\s*(?:(?:S\.|Satz)\s*(\d+))?\s*(?:(?:Nr\.|Nummer)\s*(\d+(?:\w\b)?))?\s*(?:(?:lit\.|litera|Buchst\.|Buchstabe)\s*([a-z]?))?.{0,10}?(\b[A-Z][A-Za-z]*[A-Z](?:(?:\s|\b)[XIV]+)?)/'
```

**Note**: Well, more or less - for the latest revision, please refer to `src/Traits/Regex.php`!


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


## Credits

The regular expression used in this library is based on the [`jura_regex`](https://github.com/kiersch/jura_regex) regex package by Philipp Kiersch (originally written in Python).


## Special Thanks

I'd like to thank everybody that's making free & open source software - you people are awesome. Also I'm always thankful for feedback and bug reports :)

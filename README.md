# php-gesetze

Linking german legal norms, dependency-free & GDPR-friendly. `php-gesetze` automatically transforms legal references into `a` tags - batteries included.


## Usage

The following functions are available:


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

**Note:** In contrast to `analyze`, all other function only match & link *valid* laws & legal norms! For more information, see `validate()`.


### `validate(array $array): bool`

Validates a single legal norm (across all providers):

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz;

var_dump($obj->validate($obj::analyze('§ 433 II BGB')));

# bool(true)

foreach ($obj->extract('While § 433 II BGB exists, Art. 4c GG does not!') as $match) {
    var_dump($obj->validate($match['meta']);
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


### `extract(string $string, bool $roman2arabic = false): array`

Extracts legal norms from text:

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz;

var_dump($obj->extract('This is a simple text, featuring § 1 I Nr. 1 BGB as well as Art. 4 GG'));

# array(2) {
#   [0]=>
#   array(2) {
#     ["full"]=>
#     string(19) "§ 1 I Nr. 1 BGB"
#     ["meta"]=>
#     array(6) {
#       ["norm"]=>
#       string(4) "1"
#       ["absatz"]=>
#       string(1) "I"
#       ["satz"]=>
#       string(0) ""
#       ["nr"]=>
#       string(1) "1"
#       ["lit"]=>
#       string(0) ""
#       ["gesetz"]=>
#       string(3) "BGB"
#     }
#   }
#   [1]=>
#   array(2) {
#     ["full"]=>
#     string(14) "Art. 4 GG"
#     ["meta"]=>
#     array(6) {
#       ["norm"]=>
#       string(2) "4"
#       ["absatz"]=>
#       string(0) ""
#       ["satz"]=>
#       string(0) ""
#       ["nr"]=>
#       string(0) ""
#       ["lit"]=>
#       string(0) ""
#       ["gesetz"]=>
#       string(2) "GG"
#     }
#   }
# }
```


### `linkify(string $string): string`

Transforms legal references into HTML link tags:

```php
$obj = new \S1SYPHOS\Gesetze\Gesetz;

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
$obj = new \S1SYPHOS\Gesetze\Gesetz;

# Transform text
echo $obj->linkify($text);

# <div>This is a <b>simple</b> HTML text.
# It contains legal norms, like <a href="https://www.gesetze-im-internet.de/gg/art_12.html" target="_blank">Art. 12 I GG</a>.
# .. or <a href="https://www.gesetze-im-internet.de/bgb/__433.html" target="_blank">§ 433 II nr. 2 BGB</a>!
# </div>
```

**Note:** Caching the result (to avoid repeated lookups & save resources) is beyond the scope of this library and therefore totally up to you!


## Credits

This library is based on ..

- .. an adapted (and somewhat improved) version of the [`jura_regex`](https://github.com/kiersch/jura_regex) regex package by Philipp Kiersch (originally written in Python).
- .. an adapted (and somewhat modified) version of the [`gesetze`](https://github.com/matejgrahovac/gesetze) crawler package by Matej Grahovac (originally written in Python).


## Special Thanks

I'd like to thank everybody that's making free & open source software - you people are awesome. Also I'm always thankful for feedback and bug reports :)

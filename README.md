# php-gesetze

Linking texts with gesetze-im-internet.de, no fuss

WIP


## Usage

Getting information on a single legal norm can easily be achieved by the (static) `analyze` helper function:

```php
$result = \S1SYPHOS\GesetzeImInternet::analyze('Art. 1 II GG');

var_dump($result);

# Result:
#
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

You may want to transform roman into arabic numerals, look no further: `roman2arabic` has got your back:

```php
echo \S1SYPHOS\GesetzeImInternet::roman2arabic('IX');

# Result
#
# 9
```

Want to `extract` legal norms from a text? Here you go:

```php
echo \S1SYPHOS\GesetzeImInternet::extract('This is a simple text, featuring § 1 I Nr. 1 BGB as well as Art. 4 GG');

# Result
#
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

For those wanting to replace all legal norms with links to their corresponding `gesetze-im-internet.de` websites, `linkify` is the right choice:

```php
$obj = new \S1SYPHOS\GesetzeImInternet;

echo $obj->linkify('This is a simple text, featuring § 1 I Nr. 1 BGB as well as Art. 4c GG');

# Result:
#
# This is a simple text, featuring <a href="https://www.gesetze-im-internet.de/bgb/__1.html" title="§ 1 Beginn der Rechtsfähigkeit">§ 1 I Nr. 1 BGB</a> as well as Art. 4c GG
```

**Note:** As you can see, in contrast to `analyze` & `extract`, this function only matches & links existing legal norms!


## Example

```php
include_once 'vendor/autoload.php';

# Insert test string
$text  = '<div>';
$text .= 'This is a <b>simple</b> HTML text.';
$text .= 'It contains legal norms, like Art. 12 I GG.';
$text .= '.. or § 433 II nr. 2 BGB!';
$text .= '</div>';

$obj = new \S1SYPHOS\GesetzeImInternet;
$text = $obj->linkify($text);

echo $text;

# Result:
#
# <div>This is a <b>simple</b> HTML text.
# It contains legal norms, like <a href="https://www.gesetze-im-internet.de/gg/art_12.html" target="_blank">Art. 12 I GG</a>.
# .. or <a href="https://www.gesetze-im-internet.de/bgb/__433.html" target="_blank">§ 433 II nr. 2 BGB</a>!
# </div>
```


## Credits

This library is based on ..

- .. an adapted (and somewhat improved) version of the [`jura_regex`](https://github.com/kiersch/jura_regex) regex package by Philipp Kiersch (originally written in Python).
- .. an adapted (and somewhat modified) version of the [`gesetze`](https://github.com/matejgrahovac/gesetze) crawler package by Matej Grahovac (originally written in Python).

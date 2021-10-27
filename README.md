# php-gesetze

Linking texts with gesetze-im-internet.de, no fuss

WIP


## Example

```php
include_once 'vendor/autoload.php';

# Insert test string
$text  = '<div>';
$text .= 'This is a <strong>simple</strong> HTML text.';
$text .= 'It contains legal norms, like Art. 12 I GG.';
$text .= '.. or § 433 II nr. 2 BGB!';
$text .= '</div>';

$obj = new \S1SYPHOS\GesetzeImInternet;
$new = $obj->linkify($text);

var_dump($new);

# Result:
#
# <div>This is a <strong>simple</strong> HTML text.
# It contains legal norms, like <a href="https://www.gesetze-im-internet.de/gg/art_12.html">Art. 12 I GG</a>.
# .. or <a href="https://www.gesetze-im-internet.de/bgb/__433.html">§ 433 II nr. 2 BGB</a>!
# </div>
```


## Credits

Law regex from https://github.com/kiersch/jura_regex
Python crawler https://github.com/matejgrahovac/gesetze

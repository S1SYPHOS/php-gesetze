<?php

namespace S1SYPHOS\Gesetze\Providers;

use Exception;


/**
 * Class Factory
 *
 * Creates driver instances
 */
class Factory
{
    /**
     * Available drivers
     *
     * @var array
     */
    public static $types = [
        # (1) 'gesetze-im-internet.de'
        'gesetze' => 'S1SYPHOS\Gesetze\Providers\Provider\GesetzeImInternet',
        # (2) 'dejure.org'
        'dejure' => 'S1SYPHOS\Gesetze\Providers\Provider\DejureOnline',
        # (3) 'buzer.de'
        'buzer' => 'S1SYPHOS\Gesetze\Providers\Provider\Buzer',
        # (4) 'lexparency.de'
        'lexparency' => 'S1SYPHOS\Gesetze\Providers\Provider\Lexparency',
    ];


    /**
     * Creates a new 'Provider' instance for the given type
     *
     * @param string $type
     * @return mixed
     * @throws \Exception
     */
    public static function create(string $type)
    {
        # Fail early for invalid drivers
        if (!isset(static::$types[$type])) {
            throw new Exception(sprintf('Invalid driver type: "%s"', $type));
        }

        # Instantiate object
        return new static::$types[$type];
    }
}

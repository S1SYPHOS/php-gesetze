<?php

namespace S1SYPHOS\Gesetze\Providers\Provider;

use S1SYPHOS\Gesetze\Providers\Provider;


/**
 * Class GesetzeImInternet
 *
 * Provider for 'gesetze-im-internet.de'
 */
class GesetzeImInternet extends Provider
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = 'gesetze';


    /**
     * Base URL
     *
     * @var string
     */
    protected $url = 'https://www.gesetze-im-internet.de';
}

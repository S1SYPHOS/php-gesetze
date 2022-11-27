<?php

namespace S1SYPHOS\Gesetze\Providers\Provider;

use S1SYPHOS\Gesetze\Providers\Provider;


/**
 * Class DejureOnline
 *
 * Provider for 'dejure.org'
 */
class DejureOnline extends Provider
{
    /**
     * Properties
     */

    /**
     * Individual identifier
     *
     * @var string
     */
    protected $identifier = 'dejure';


    /**
     * Base URL
     *
     * @var string
     */
    protected $url = 'https://dejure.org';
}

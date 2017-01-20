<?php
/**
 * This file is part of the Passport package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// set the default timezone
date_default_timezone_set('UTC');

// create new session
session_start();

$path = dirname(__FILE__);

require realpath($path . '/../autoload.php');
require realpath($path . '/../vendor/autoload.php');

function getKeys($path)
{
    $location = $path . '/.keys';

    if ( ! is_readable($location)) {
        throw new UnexpectedValueException('Passport test client and service keys required: ' . $location);
    }

    $data = [];

    foreach (file($location) as $line) {
        list($key, $value) = explode(':', $line, 2);
        $data[$key] = trim($value);
    }

    return $data;
};

$keys = getKeys($path);

define('PERSONA_ID', '1932c744-b578-11e6-9eed-000ffef5a155');
define('APPLICATION_ID', 'cdc0b460-dbda-11e5-8dc8-cb080c529875');
define('APPLICATION_NAME', 'Accounts');
define('APPLICATION_REFERENCE', 'accounts');
define('USER_ID', '1e024dbd-62e0-4ea0-9733-8e9c27c5fce5');
define('USER_EMAIL', 'mochajs@example.org');
define('USER_PASSWORD', 'mochajs@example.org');
define('USER_NAME', 'Mocha');
define('USER_STATUS', 'Active');

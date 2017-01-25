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

// Passport API
define('PASSPORT_ID',     $keys['id']);
define('PASSPORT_SECRET', $keys['secret']);
define('PASSPORT_HOST',   'passport.eurolink.dev');
define('PASSPORT_SCHEME', 'http');

$options = [
    'host' => PASSPORT_HOST,
    'scheme' => PASSPORT_SCHEME,
    'credentials' => [
        'id' => PASSPORT_ID,
        'key' => PASSPORT_SECRET,
        'algorithm' => 'sha256'
    ]
];

define('APPLICATION_ID', $keys['id']);
define('APPLICATION_NAME', 'Test');
define('APPLICATION_REFERENCE', 'test');
define('PERSONA_ID', '1932c744-b578-11e6-9eed-000ffef5a155');
define('USER_ID', '1e024dbd-62e0-4ea0-9733-8e9c27c5fce5');
define('USER_EMAIL', 'mochajs@example.org');
define('USER_PASSWORD', 'mochajs@example.org');
define('USER_NAME', 'Mocha');
define('USER_STATUS', 'Active');
define('INVALID_ID', 'fff0000f-0000-0000-00ff-00000f00f00f');

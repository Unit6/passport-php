<?php
/*
 * This file is part of the Passport package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Unit6\Passport;

/**
 * Test Passport Application Endpoints
 *
 * Check for correct operation of the standard features.
 */
class ApplicationTest extends PHPUnit_Framework_TestCase
{
    protected $client;

    public function setUp()
    {
        global $keys;

        $this->client = new Passport\Client($keys);
    }

    public function tearDown()
    {
        unset($this->client);
    }

    public function testGetApplicationByID()
    {
        $applicationId = APPLICATION_ID;

        $application = $this->client->getApplication($applicationId);

        $this->assertInstanceOf('Unit6\Passport\Application', $application);
        $this->assertInstanceOf('Unit6\Passport\Client', $application->getClient());

        return $application;
    }

    public function testFindApplicationsByReference()
    {
        $params = [
            'reference' => APPLICATION_REFERENCE
        ];

        $collection = $this->client->findApplications($params);

        $this->assertNotEmpty($collection);
        $this->assertArrayHasKey('count', $collection);
        $this->assertArrayHasKey('rows', $collection);
    }
}
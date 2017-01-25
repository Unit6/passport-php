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
        global $options;

        $this->client = new Passport\Client($options);
    }

    public function tearDown()
    {
        unset($this->client);
    }

    public function testGetLoginUrl()
    {
        $url = $this->client->getLoginUrl();

        $parts = parse_url($url);

        $this->assertEquals(PASSPORT_SCHEME, $parts['scheme']);
        $this->assertEquals(PASSPORT_HOST, $parts['host']);
        $this->assertArrayHasKey('query', $parts);

        $query = [];
        parse_str($parts['query'], $query);

        $this->assertArrayHasKey('app', $query);
        $this->assertNotEmpty($query['app']);
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
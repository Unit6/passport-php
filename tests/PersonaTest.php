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
 * Test Passport Persona Endpoints
 *
 * Check for correct operation of the standard features.
 */
class PersonaTest extends PHPUnit_Framework_TestCase
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

    public function testFindPersonasByUserEmail()
    {
        $params = [
            'user_email' => USER_EMAIL
        ];

        $collection = $this->client->findPersonas($params);

        $this->assertNotEmpty($collection);
        $this->assertArrayHasKey('count', $collection);
        $this->assertArrayHasKey('rows', $collection);
    }

    public function testGetPersonaByID()
    {
        $personaId = PERSONA_ID;

        $persona = $this->client->getPersona($personaId);

        $this->assertInstanceOf('Unit6\Passport\Persona', $persona);
        $this->assertInstanceOf('Unit6\Passport\Client', $persona->getClient());

        return $persona;
    }

    /**
     * @depends testGetPersonaByID
     */
    public function testGetPersonaTokenFromApplication(Passport\Persona $persona)
    {
        $application = $persona->getApplication();

        $this->assertInstanceOf('Unit6\Passport\Application', $application);

        $token = $application->getPersonaToken($persona);

        $this->assertNotEmpty($token);
    }

    /**
     * @depends testGetPersonaByID
     */
    public function testGetPersonaToken(Passport\Persona $persona)
    {
        $token = $persona->getToken();

        $this->assertNotEmpty($token);

        $application = $persona->getApplication();

        $token = $application->getPersonaToken($persona);

        $this->assertNotEmpty($token);

        return $token;
    }

    /**
     * @depends testGetPersonaByID
     * @depends testGetPersonaToken
     */
    public function testGetPersonaFromToken(Passport\Persona $persona, $token)
    {
        $application = $persona->getApplication();

        $this->assertInstanceOf('Unit6\Passport\Application', $application);

        $persona = $application->getPersonaByToken($token);

        $this->assertInstanceOf('Unit6\Passport\Persona', $persona);
    }

    public function testRegisterPersona()
    {
        $applicationId = APPLICATION_ID;

        $application = $this->client->getApplication($applicationId);

        $this->assertInstanceOf('Unit6\Passport\Application', $application);

        $persona = (new Passport\Persona())
            ->withEmail('j.smith@example.org')
            ->withApplication($application);

        $this->assertEmpty($persona->getId());

        $registered = $this->client->registerPersona($persona);

        $this->assertTrue($registered);
        $this->assertNotEmpty($persona->getId());

        $message = $this->client->getMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('persona_registered', $message['slug']);

        return $persona;
    }
}
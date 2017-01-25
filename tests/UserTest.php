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
 * Test Passport User Endpoints
 *
 * Check for correct operation of the standard features.
 */
class UserTest extends PHPUnit_Framework_TestCase
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

    public function testFindUsersByEmail()
    {
        $params = [
            'email' => USER_EMAIL
        ];

        $collection = $this->client->findUsers($params);

        $this->assertNotEmpty($collection);
        $this->assertArrayHasKey('count', $collection);
        $this->assertArrayHasKey('rows', $collection);
    }

    public function testCreateUserWithClient()
    {
        $user = (new Passport\User())
            ->withClient($this->client);

        $this->assertInstanceOf('Unit6\Passport\User', $user);
        $this->assertInstanceOf('Unit6\Passport\Client', $user->getClient());

        return $user;
    }

    /**
     * @depends testCreateUserWithClient
     */
    public function testAuthenticateUserInvalidError(Passport\User $user)
    {
        $email = 'foobar@example.org';
        $password = 'foobar';

        $user = $user
            ->withEmail($email)
            ->withPassword($password);

        $isAuthenticated = $user->authenticate();

        $this->assertFalse($isAuthenticated);

        $message = $user->getClientMessage();

        $this->assertEquals('error', $message['type']);
        $this->assertEquals('user_invalid', $message['slug']);
    }

    /**
     * @depends testCreateUserWithClient
     */
    public function testSuccessAuthenticateUserIsActive(Passport\User $user)
    {
        $email = USER_EMAIL;
        $password = USER_PASSWORD;

        $user = $user
            ->withEmail($email)
            ->withPassword($password);

        $isAuthenticated = $user->authenticate();

        $this->assertTrue($isAuthenticated);
        $this->assertTrue($user->isActive());

        $message = $user->getClientMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('authentication_successful', $message['slug']);
    }

    public function testGetUserById()
    {
        $userId = USER_ID;

        $user = $this->client->getUser($userId);

        $this->assertInstanceOf('Unit6\Passport\User', $user);

        $message = $this->client->getMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('user_found', $message['slug']);

        return $user;
    }

    /**
     * @depends testGetUserById
     */
    public function testGetUsersApplications(Passport\User $user)
    {
        $applications = $user->getApplications();

        $this->assertNotEmpty($applications);

        $message = $user->getClientMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('applications_found', $message['slug']);

        return $applications;
    }

    /**
     * @depends testGetUserById
     * @depends testGetUsersApplications
     */
    public function testGetUserPersonaForApplication(Passport\User $user, array $applications)
    {
        $applicationId = $applications[0]['application_id'];

        $persona = $user->getPersona($applicationId);

        $this->assertInstanceOf('Unit6\Passport\Persona', $persona);

        $message = $user->getClientMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('persona_found', $message['slug']);
    }

    /**
     * @depends testGetUserById
     */
    public function testUpdateUserProfile(Passport\User $user)
    {
        $profile = [
            'name' => USER_NAME
        ];

        $updated = $user->update($profile);

        $this->assertTrue($updated);

        $message = $user->getClientMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('user_updated', $message['slug']);
    }

    /**
     * @depends testGetUserById
     */
    public function testUpdateUserPassword(Passport\User $user)
    {
        $updated = $user->updatePassword(USER_PASSWORD);

        $this->assertTrue($updated);

        $message = $user->getClientMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('user_updated', $message['slug']);
    }

    /**
     * @depends testGetUserById
     */
    public function testUserActivate(Passport\User $user)
    {
        $updated = $user->activate();

        $this->assertTrue($updated);

        $message = $user->getClientMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('user_updated', $message['slug']);
    }

    /**
     * @depends testGetUserById
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnUpdateUserStatusWithInvalidStatus(Passport\User $user)
    {
        $user->updateStatus('Foobar');
    }

    /**
     * @depends testGetUserById
     */
    public function testErrorOnUserRegistrationWithExistingEmail(Passport\User $user)
    {
        $registered = $this->client->registerUser($user);

        $this->assertFalse($registered);

        $message = $this->client->getMessage();

        $this->assertEquals('error', $message['type']);
        $this->assertEquals('user_email_taken', $message['slug']);
    }

    public function testUserRegistration()
    {
        $user = (new Passport\User())
            ->withName('John Smith')
            ->withEmail('j.smith@example.org')
            ->withPassword('j.smith@example.org')
            ->withStatus('Active');

        $this->assertEmpty($user->getId());

        $registered = $this->client->registerUser($user);

        $this->assertTrue($registered);
        $this->assertNotEmpty($user->getId());

        $message = $this->client->getMessage();

        $this->assertEquals('success', $message['type']);
        $this->assertEquals('user_registered', $message['slug']);

        return $user;
    }

    /**
     * @depends testUserRegistration
     */
    public function testDeleteRegisteredUser(Passport\User $user)
    {
        $deleted = $user->delete($force = 1);

        $this->assertTrue($deleted);
    }
}
<?php
/**
 * This file is part of the Passport package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

    require 'config.php';

    use Unit6\Passport;

    $client = new Passport\Client($keys);

    $name = USER_NAME;
    $email = USER_EMAIL;
    $password = USER_PASSWORD;
    $status = USER_STATUS;

    /*
    $user = $client->getUser('a7cb5320-4a0e-4aad-be18-4b17ea04909f');
    $deleted = $user->delete($force = 1);
    var_dump($deleted);
    */

    /*
    $user = (new Passport\User())
        ->withName('John Smith')
        ->withEmail('j.smith@example.org')
        ->withPassword('j.smith@example.org')
        ->withStatus('Active');

    $registered = $client->registerUser($user);

    var_dump(__FILE__, $registered, $user->getId(), $client->getMessage()); exit;
    */

    /*
    $user = (new Passport\User())
        ->withClient($client)
        ->withEmail($email)
        ->withPassword($password);

    $user->authenticate();

    $applicationId = APPLICATION_ID;

    $persona = $user->getPersona($applicationId);

    #$update = $user->updateStatus(Passport\User::STATUS_ACTIVE);
    */

    /*
    $personaId = PERSONA_ID;

    $persona = $client->getPersona($personaId);

    var_dump(__FILE__, $persona, $client->getMessage()); exit;
    */

    $applicationId = APPLICATION_ID;

    $application = $client->getApplication($applicationId);

    $persona = (new Passport\Persona())
        ->withEmail('j.smith@example.org')
        ->withApplication($application);

    $registered = $client->registerPersona($persona);

    var_dump(__FILE__, $registered, $persona->getId(), $client->getMessage()); exit;


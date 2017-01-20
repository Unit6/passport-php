<?php
/*
 * This file is part of the Passport package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit6\Passport;

use InvalidArgumentException;
use RuntimeException;
use UnexpectedValueException;

use Unit6\HTTP\Body;
use Unit6\HTTP\Client\Request;
use Unit6\HTTP\Client\Response;
use Unit6\HTTP\Headers;
use Unit6\HTTP\URI;
use Unit6\Hawk;

/**
 * Passport Client Class
 *
 * Create a client instance.
 */
final class Client
{
    /**
     * API Version
     *
     * @var string
     */
    const VERSION = 'v1';

    /**
     * API Protocol
     *
     * @var string
     */
    const PROTOCOL = 'https';

    /**
     * API Host
     *
     * @var string
     */
    const HOST = 'api.passport.eurolink.co';

    /**
     * API Content Type
     *
     * @var string
     */
    const CONTENT_TYPE = 'application/json';

    /**
     * API Credentials
     *
     * @var array
     */
    private $credentials;

    /**
     * API User Agent
     *
     * @var string
     */
    private $userAgent;

    /**
     * API Message from Last Request
     *
     * @var array
     */
    private $message;

    /**
     * Client constructor
     *
     * @param array $credentials Your Passport client and service keys
     *
     * @return void
     */
    public function __construct(array $credentials)
    {
        if ( ! function_exists('curl_init')) {
            throw new RuntimeException('cURL is required');
        }

        if ( ! function_exists('json_decode')) {
            throw new RuntimeException('JSON is required');
        }

        if ( empty($credentials)) {
            throw new InvalidArgumentException('Passport credentials required');
        }

        if ( ! isset($credentials['id']) || empty($credentials['id'])) {
            throw new UnexpectedValueException('Passport "id" missing');
        }

        if ( ! isset($credentials['key']) || empty($credentials['key'])) {
            throw new UnexpectedValueException('Passport "key" missing');
        }

        if ( ! isset($credentials['algorithm']) || empty($credentials['algorithm'])) {
            throw new UnexpectedValueException('Passport "algorithm" missing');
        }

        $this->credentials = $credentials;
    }

    /**
     * Get API Endpoint
     *
     * @param string $resource API resource to request.
     * @param string $method   HTTP request method.
     * @param array  $data     HTTP request data.
     *
     * @return string
     */
    public function getEndpointUrl($resource, $method, array &$data = [])
    {
        $endpoint = self::PROTOCOL . '://' . self::HOST . '/' . self::VERSION . '/' . $resource;

        $uri = URI::parse($endpoint);

        if ($method === 'GET') {
            $uri = $uri->withQuery(http_build_query($data));
            $data = [];
        }

        return sprintf('%s', $uri);
    }

    /**
     * Get API Content-Type
     *
     * @return string
     */
    public function getContentType()
    {
        return self::CONTENT_TYPE;
    }

    /**
     * Get API Credentials
     *
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Set Timeout
     *
     * @param int $timeout
     *
     * @return void
     */
    public function setTimeout($timeout = 3)
    {
        $this->timeout = $timeout;
    }

    /**
     * Generate User Agent for API Request
     *
     * @return string
     */
    private function getUserAgent()
    {
        if ($this->userAgent) {
            return $this->userAgent;
        }

        $arch = (bool)((1<<32)-1) ? 'x64' : 'x86';

        $data = [
            'os.name'      => php_uname('s'),
            'os.version'   => php_uname('r'),
            'os.arch'      => $arch,
            'lang'         => 'php',
            'lang.version' => phpversion(),
            #'lib.version'  => self::VERSION,
            'api.version'  => self::VERSION,
            'owner'        => 'unit6/passport'
        ];

        $this->userAgent = http_build_query($data, '', ';');

        return $this->userAgent;
    }

    /**
     * Get Last API Response Message
     *
     * @return $array
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set Last API Response Message
     *
     * @param array $message Response from API containing a type, slug and text.
     *
     * @return void
     */
    public function setMessage(array $message)
    {
        $this->message = $message;
    }

    /**
     * Get Authorization Header
     *
     * Calculate the authorization header for the request using
     * client credentials.
     *
     * @param string $uri     Full endpoint URI.
     * @param string $method  Valid HTTP method
     * @param array  $payload Request body to be sent.
     *
     * @return string
     */
    private function getAuthorization($uri, $method, $payload)
    {
        $options = [
            'content_type' => $this->getContentType(),
            'credentials' => $this->getCredentials(),
            'payload' => $payload
        ];

        return Hawk\Client::header($uri, $method, $options);
    }

    /**
     * Get Authentication from Response
     *
     * @param Response $response API response.
     *
     * @return string
     */
    private function isAuthenticated(Response $response, array $artifacts)
    {
        $options = [
            'headers' => [
                'content-type' => $response->getHeaderLine('content-type'),
                'server-authorization' => $response->getHeaderLine('server-authorization')
            ]
        ];

        return Hawk\Client::authenticate($options, $this->getCredentials(), $artifacts);
    }

    /**
     * Sends request to Passport API
     *
     * @param string $method   Valid HTTP method
     * @param string $resource URI of API resource
     * @param array  $data     Payload parameters.
     *
     * @return array
     */
    public function request($method, $resource, array $data = [])
    {
        $url = $this->getEndpointUrl($resource, $method, $data);

        $body = (empty($data) ? null : Body::toJSON($data));

        $authorization = $this->getAuthorization($url, $method, $body);

        $headers = new Headers();
        $headers->set('Authorization', $authorization['field']);
        $headers->set('Content-Type', $this->getContentType());
        #$headers->set('X-Passport-User-Agent', $this->getUserAgent());

        $request = new Request($method, $url, $headers, $body);

        // other cURL options.
        $options = [];

        try {
            $response = $request->send($options);
        } catch (UnexpectedValueException $e) {
            var_dump($e->getMessage()); exit;

            // handle cURL related errors.
            // see: http://php.net/curl.constants#93950
            $errno = $e->getCode();

            if ($errno === CURLE_SSL_CACERT) { // 60
                // Peer certificate cannot be authenticated with known CA certificates.
                throw new GatewayException(sprintf('Passport SSL certificate could not be validated; %s', $e->getMessage()), $errno);
            } elseif ($errno === CURLE_OPERATION_TIMEOUTED) { // 28 or CURLE_OPERATION_TIMEDOUT.
                // Operation timeout. The specified time-out period was reached according to the conditions.
                throw new GatewayException(sprintf('Passport timeout or possible order failure; %s', $e->getMessage()), $errno);
            } else {
                throw new GatewayException(sprintf('Passport is currently unavailable, please try again later; %s', $e->getMessage()), $errno);
            }
        }

        if ( ! $this->isAuthenticated($response, $authorization['artifacts'])) {
            throw new GatewayException('Passport response authentication failed');
        }

        $contents = $response->getBody()->getContents();
        $result = json_decode($contents, $assoc = true);

        if ($errno = json_last_error()) {
            throw new GatewayException(sprintf('Passport response JSON malformed; %s', json_last_error_msg()), $errno);
        }

        /*
        echo 'Content-Type: ' . $response->getHeaderLine('Content-Type') . PHP_EOL;
        echo 'Status Code: ' . $response->getStatusCode() . PHP_EOL;
        echo 'Reason Phrase: ' . $response->getReasonPhrase() . PHP_EOL;
        echo 'Contents: ' . PHP_EOL . $contents . PHP_EOL;
        exit;
        */

        if (isset($result['message'])) {
            $this->setMessage($result['message']);
        }

        return [
            'statusCode' => $response->getStatusCode(),
            'reasonPhrase' => $response->getReasonPhrase(),
            'content' => $result['content']
        ];
    }

    /**
     * Get Users by Criteria
     *
     * @param string $params
     *
     * @return array
     */
    public function findUsers(array $params = null)
    {
        User::parseCriteria($params);

        $response = $this->request('GET', 'users', $params);

        return $response['content'];
    }

    /**
     * Get User by ID
     *
     * User details can be obtained by sending an Identifier.
     *
     * @param string $userId Valid user UUID.
     *
     * @return User
     */
    public function getUser($userId)
    {
        $response = $this->request('GET', sprintf('users/%s', $userId));

        return User::parse($response['content'], $this);
    }

    /**
     * Register User Profile
     *
     * @param User $user User profile to register.
     *
     * @return Persona
     */
    public function registerUser(User &$user)
    {
        $data = $user->getRegistration();

        $response = $this->request('POST', 'users/register', $data);

        $success = ($response['reasonPhrase'] === 'Created');

        if ($success) $user = User::parse($response['content'], $this);

        return $success;
    }

    /**
     * Update User
     *
     * @param string $userId  User UUID.
     * @param array  $profile User profile data.
     *
     * @return bool
     */
    public function updateUser($userId, array $profile)
    {
        $whitelist = ['name', 'email', 'status', 'password'];

        $data = [];

        foreach ($whitelist as $key) {
            if (isset($profile[$key])) $data[$key] = $profile[$key];
        }

        $response = $this->request('PATCH', sprintf('users/%s', $userId), $data);

        return ($response['reasonPhrase'] === 'OK');
    }

    /**
     * Update User Profile
     *
     * @param string $userId  User UUID.
     * @param array  $profile User profile data.
     *
     * @return bool
     */
    public function updateUserProfile($userId, array $profile)
    {
        $whitelist = ['name', 'email'];

        $data = [];

        foreach ($whitelist as $key) {
            if (isset($profile[$key])) $data[$key] = $profile[$key];
        }

        $response = $this->request('PATCH', sprintf('users/%s/profile', $userId), $data);

        return ($response['reasonPhrase'] === 'OK');
    }

    /**
     * Update User Password
     *
     * @param string $userId   User UUID.
     * @param string $password User password.
     *
     * @return bool
     */
    public function updateUserPassword($userId, $password)
    {
        $data = ['password' => $password];

        $response = $this->request('PATCH', sprintf('users/%s/password', $userId), $data);

        return ($response['reasonPhrase'] === 'OK');
    }

    /**
     * Update User Status
     *
     * @param string $status Set status.
     *
     * @return bool
     */
    public function updateUserStatus($userId, $status)
    {
        User::parseStatus($status);

        $data = ['status' => $status];

        $response = $this->request('PATCH', sprintf('users/%s/status', $userId), $data);

        return ($response['reasonPhrase'] === 'OK');
    }

    /**
     * Get Personas by Criteria
     *
     * @param string $params
     *
     * @return array
     */
    public function findPersonas(array $params = null)
    {
        Persona::parseCriteria($params);

        $response = $this->request('GET', 'personas', $params);

        return $response['content'];
    }

    /**
     * Get Persona by ID
     *
     * Persona details can be obtained by sending an Identifier.
     *
     * @param string $personaId Valid persona UUID.
     *
     * @return Persona
     */
    public function getPersona($personaId)
    {
        $response = $this->request('GET', sprintf('personas/%s', $personaId));

        return Persona::parse($response['content'], $this);
    }

    /**
     * Get Applications by Criteria
     *
     * @param string $params
     *
     * @return array
     */
    public function findApplications(array $params = null)
    {
        Application::parseCriteria($params);

        $response = $this->request('GET', 'applications', $params);

        return $response['content'];
    }

    /**
     * Get Application by ID
     *
     * Application details can be obtained by sending an Identifier.
     *
     * @param string $applicationId Valid applications UUID.
     *
     * @return Application
     */
    public function getApplication($applicationId)
    {
        $response = $this->request('GET', sprintf('applications/%s', $applicationId));

        return Application::parse($response['content'], $this);
    }

    /**
     * Get Applications by User ID.
     *
     * List of applications user is associated with.
     *
     * @param string $userId Valid user UUID.
     *
     * @return array
     */
    public function getApplicationsByUser($userId)
    {
        $applications = [];

        $response = $this->request('GET', sprintf('users/%s/applications', $userId));

        if ($response['reasonPhrase'] === 'OK') {
            $applications = $response['content'];
        }

        return $applications;
    }

    /**
     * Register Persona for Application
     *
     * @param string  $applicationId Application UUID.
     * @param Persona $persona       Persona profile to register.
     *
     * @return Persona
     */
    public function registerPersona(Persona &$persona)
    {
        $data = $persona->getRegistration();

        $response = $this->request('POST', 'personas/register', $data);

        $success = ($response['reasonPhrase'] === 'Created' ||
                    $response['reasonPhrase'] === 'OK');

        if ($success) $persona = Persona::parse($response['content'], $this);

        return $success;
    }

    /**
     * Get Persona by Application and User.
     *
     * @param string  $userId        User UUID.
     * @param string  $applicationId Application UUID.
     *
     * @return Persona
     */
    public function getPersonaByApplicationUser($userId, $applicationId)
    {
        $uri = sprintf('users/%s/applications/%s/persona', $userId, $applicationId);

        $response = $this->request('GET', $uri);

        return Persona::parse($response['content'], $this);
    }
}

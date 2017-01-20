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
use UnexpectedValueException;

use Unit6\JWT;

/**
 * Application Class
 *
 * Create an application resource.
 */
class Application extends AbstractResource
{
    /**
     * Application UUID
     *
     * A unique identifier for the application.
     *
     * @var string
     */
    protected $id;

    /**
     * Application Name
     *
     * @var string
     */
    protected $name;

    /**
     * Application Secret
     *
     * @var string
     */
    protected $secret;

    /**
     * Application Return URL
     *
     * @var string
     */
    protected $return_url;

    /**
     * Application Status
     *
     * @var string
     */
    protected $status;

    /**
     * Application Statuses
     *
     * @var array
     */
    public static $statuses = ['Active', 'Pending', 'Achived'];

    /**
     * Application Updated Date
     *
     * @var string
     */
    protected $updated_at;

    /**
     * Application Created Date
     *
     * @var string
     */
    protected $created_at;

    /**
     * Application Deleted Date
     *
     * @var string
     */
    protected $deleted_at;

    /**
     * Creating a Application
     *
     * @param array $params Application parameters
     *
     * @return Application
     */
    public function __construct(array $params = [])
    {
        if ( ! empty($params)) $this->setParameters($params);
    }

    /**
     * Get Application Identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Application Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Application Secret
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get Persona Return URL
     *
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * Get Persona Parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'secret' => $this->getSecret(),
            'return_url' => $this->getReturnUrl()
        ];
    }

    /**
     * Format Application Parameters
     *
     * @return array
     */
    public function formatParameters(&$params)
    {
        $params['id'] = $this->getId();
        $params['name'] = $this->getName();
        $params['secret'] = $this->getSecret();
        $params['return_url'] = $this->getReturnUrl();
    }

    /**
     * Parse Application Criteria
     *
     * @param array $criteria
     *
     * @return void
     */
    public static function parseCriteria(array &$criteria = null)
    {
        $whitelist = [
            'id',
            'name',
            'reference',
            'public',
            'status',
            'algorithm'
        ];

        foreach ($criteria as $key => $value) {
            if ( ! in_array($key, $whitelist)) {
                throw new UnexpectedValueException(sprintf('Invalid application criterion: %s', $key));
            }
        }
    }

    /**
     * Create Instance from API Response
     *
     * @param array       $content Result from API request
     * @param Client|null $client  API Client
     *
     * @return self
     */
    public static function parse(array $content, Client $client = null)
    {
        $instance = null;

        if (isset($content['id'])) {
            $instance = new self($content);
            if ($client) $instance = $instance->withClient($client);
        }

        return $instance;
    }

    /**
     * Get Persona Encoded as JWT Token
     *
     * @param Persona $persona
     *
     * @return string
     */
    public function getPersonaToken(Persona $persona)
    {
        $claims = [
            'message' => $persona->getParameters(),
            'iat' => time()
        ];

        $options = [
            'key' => $this->getSecret(),
            'alg' => 'HS256'
        ];

        return (new JWT\Encode($claims, $options))->getToken();
    }

    /**
     * Get Decoded Persona from JWT Token
     *
     * @param string $token  Encoded JWT.
     * @param int    $leeway Time skew in seconds.
     *
     * @return Persona
     */
    public function getPersonaByToken($token, $leeway = 120)
    {
        $options = [
            'key' => $this->getSecret(),
            'algorithms' => ['HS256'],
            'leeway' => $leeway
        ];

        $claims = (new JWT\Decode($token, $options))->getClaims();

        // TODO: Fix Unit6\JWT: Claims->getMessage();
        $content = json_decode(json_encode($claims->message), true);

        return Persona::parse($content, $this->getClient());
    }
}
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
 * Persona Class
 *
 * Create an persona resource.
 */
class Persona extends AbstractResource
{
    /**
     * Persona UUID
     *
     * A unique identifier for the persona.
     *
     * @var string
     */
    protected $id;

    /**
     * Persona Email
     *
     * Email address for persona.
     *
     * @var string
     */
    protected $email;

    /**
     * Persona Name
     *
     * Name for persona.
     *
     * @var string
     */
    protected $name;

    /**
     * Persona Status
     *
     * Status of persona.
     *
     * @var string
     */
    protected $status;

    /**
     * Associated Application for Persona
     *
     * @var Application
     */
    protected $application;

    /**
     * Creating a Persona
     *
     * @param array $params Persona parameters
     *
     * @return Persona
     */
    public function __construct(array $params = [])
    {
        if ( ! empty($params)) $this->setParameters($params);
    }

    /**
     * Get Persona Identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Persona Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Persona Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get Persona Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get Persona Application
     *
     * @return application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Persona Name
     *
     * @param string $name
     *
     * @return self
     */
    public function withName($name)
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    /**
     * Persona Email
     *
     * @param string $email
     *
     * @return self
     */
    public function withEmail($email)
    {
        $clone = clone $this;
        $clone->email = $email;

        return $clone;
    }

    /**
     * Persona Status
     *
     * @param string $status
     *
     * @return self
     */
    public function withStatus($status)
    {
        self::parseStatus($status);

        $clone = clone $this;
        $clone->status = $status;

        return $clone;
    }

    /**
     * Persona Application
     *
     * @param Application $application
     *
     * @return self
     */
    public function withApplication(Application $application)
    {
        $clone = clone $this;
        $clone->application = $application;

        return $clone;
    }

    /**
     * Get Persona Registration Profile
     *
     * @return array
     */
    public function getRegistration()
    {
        return [
            'id' => $this->getId(),
            'email' => $this->getEmail(),
            'application_id' => $this->getApplication()->getId()
        ];
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
            'email' => $this->getEmail(),
            'status' => $this->getStatus(),
            'application' => $this->getApplication() ? $this->getApplication()->getParameters() : null
        ];
    }

    /**
     * Format Persona Parameters
     *
     * @return array
     */
    public function formatParameters(&$params)
    {
        $params['id'] = $this->getId();
        $params['name'] = $this->getName();
        $params['email'] = $this->getEmail();
        $params['status'] = $this->getStatus();
        $params['application'] = $this->getApplication()->getParameters();
    }

    /**
     * Parse Persona Criteria
     *
     * @param array $criteria
     *
     * @return void
     */
    public static function parseCriteria(array &$criteria = null)
    {
        $whitelist = [
            'application_id',
            'application_name',
            'application_reference',
            'user_email',
            'user_id',
            'user_status',
            'user_type'
        ];

        foreach ($criteria as $key => $value) {
            if ( ! in_array($key, $whitelist)) {
                throw new UnexpectedValueException(sprintf('Invalid persona criterion: %s', $key));
            }
        }
    }

    /**
     * Create Persona from API Response
     *
     * @param array       $content Result from API request
     * @param Client|null $client  API Client
     *
     * @return self
     */
    public static function parse(array $content, Client $client = null)
    {
        $persona = null;

        if (isset($content['id'])) {
            $persona = new self($content);
            if ($client) $persona = $persona->withClient($client);
            if (isset($content['application'])) {
                $application = Application::parse($content['application']);
                if ($client) $application = $application->withClient($client);
                $persona = $persona->withApplication($application);
            }
        }

        return $persona;
    }

    /**
     * Get Persona Encoded as JWT Token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->getApplication()->getPersonaToken($this);
    }
}
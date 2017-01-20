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

use ReflectionProperty;
use DateTime;
use DateTimeZone;

/**
 * Resource Class
 *
 * Create an resource.
 */
abstract class AbstractResource
{
    /**
     * URI for resource
     *
     * @var string
     */
    protected static $uri;

    /**
     * Required parameters
     *
     * @var array
     */
    protected static $required = [];

    /**
     * Resource Parameters
     *
     * @var array
     */
    protected $params = [];

    /**
     * API Client
     *
     * @var Client
     */
    protected $client;

    /**
     * Get API Client
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Add API Client
     *
     * @param Client
     *
     * @return self
     */
    public function withClient(Client $client)
    {
        $clone = clone $this;
        $clone->client = $client;

        return $clone;
    }

    /**
     * Sends request to Passport API with Client
     *
     * @param string $method   Valid HTTP method
     * @param string $resource URI of API resource
     * @param array  $data     Payload parameters.
     *
     * @return array
     */
    protected function request($method, $resource, array $data = [])
    {
        return $this->getClient()->request($method, $resource, $data);
    }

    /**
     * Get Last Client Message from API Response
     *
     * @return array
     */
    public function getClientMessage()
    {
        return $this->getClient()->getMessage();
    }

    /**
     * Get User Updated Date
     *
     * @return string
     */
    public function getUpdatedDate()
    {
        return new DateTime($this->updated_at, new DateTimeZone('UTC'));
    }

    /**
     * Get User Created Date
     *
     * @return DateTime
     */
    public function getCreatedDate()
    {
        return new DateTime($this->created_at, new DateTimeZone('UTC'));
    }

    /**
     * Get User Deleted Date
     *
     * @return string
     */
    public function getDeletedDate()
    {
        return new DateTime($this->deleted_at, new DateTimeZone('UTC'));
    }

    /**
     * Format Resource Parameters
     *
     * @return array
     */
    public function formatParameters(&$params)
    {
        // nothing.
    }

    /**
     * Get Resource Parameter
     *
     * @return array
     */
    public function getParameters()
    {
        $params = [];

        $this->formatParameters($params);

        if (empty($params))
        {
            $params = $this->params;
        }

        $filtered = [];

        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Get order JSON
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->getParameters());
    }

    /**
     * Set Parsed Parameters
     *
     * @param array $params Resource parameters
     *
     * @return void
     */
    public function setParameters(array $params)
    {
        self::parseRequired($params);

        foreach ($params as $key => $value) {
            $property = new ReflectionProperty($this, $key);
            if ($property->isProtected()) {
                $this->$key = $value;
                $this->params[$key] = $value;
            }
        }

        #$this->params = $params;
    }

    /**
     * Parse Required Fields
     *
     * @param array $params Resource parameters
     *
     * @return void
     */
    public static function parseRequired(array $params)
    {
        if (empty($params)) {
            throw new InvalidArgumentException('Invalid parameters');
        }

        $missing = [];
        foreach (self::$required as $key) {
            if (isset($params[$key])) {
                continue;
            }

            $missing[] = $key;
        }

        if (count($missing)) {
            throw new InvalidArgumentException(sprintf('Missing required parameters: %s', implode(', ', $errors)));
        }
    }
}
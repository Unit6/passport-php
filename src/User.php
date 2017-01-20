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

/**
 * User Class
 *
 * Create an user resource.
 */
class User extends AbstractResource
{
    /**
     * User Statuses
     *
     * @var string
     */
    const STATUS_ACTIVE = 'Active';
    const STATUS_PENDING = 'Pending';
    const STATUS_ARCHIVED = 'Archived';

    /**
     * User UUID
     *
     * A unique identifier for the user/
     *
     * @var string
     */
    protected $id;

    /**
     * User Email
     *
     * Email address which should be unique for the user.
     *
     * @var string
     */
    protected $email;

    /**
     * User Status
     *
     * Status of user.
     *
     * @var string
     */
    protected $status;

    /**
     * User Statuses
     *
     * @var array
     */
    public static $statuses = ['Active', 'Pending', 'Achived'];

    /**
     * User Password
     *
     * Redacted field for users password.
     *
     * @var string
     */
    protected $password;

    /**
     * User Name
     *
     * @var string
     */
    protected $name;

    /**
     * User Type
     *
     * @var string
     */
    protected $type;

    /**
     * User Types
     *
     * @var array
     */
    public static $types = ['user'];

    /**
     * User Updated Date
     *
     * @var string
     */
    protected $updated_at;

    /**
     * User Created Date
     *
     * @var string
     */
    protected $created_at;

    /**
     * User Deleted Date
     *
     * @var string
     */
    protected $deleted_at;

    /**
     * Creating a User
     *
     * @param array $params User parameters
     *
     * @return User
     */
    public function __construct(array $params = [])
    {
        if ( ! empty($params)) $this->setParameters($params);
    }

    /**
     * Set User with Status
     *
     * @param string $status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get User Identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get User Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get User Status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is User Status Active
     *
     * @return boolean
     */
    public function isActive()
    {
        return ($this->getStatus() === self::STATUS_ACTIVE);
    }

    /**
     * Is User Status Pending
     *
     * @return boolean
     */
    public function isPending()
    {
        return ($this->getStatus() === self::STATUS_PENDING);
    }

    /**
     * Is User Status Archived
     *
     * @return boolean
     */
    public function isArchived()
    {
        return ($this->getStatus() === self::STATUS_ARCHIVED);
    }

    /**
     * Get User Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get User Type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get User Name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * User of ID
     *
     * @param string $id
     *
     * @return self
     */
    public function withId($id)
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }

    /**
     * User of Name
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
     * User of Email
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
     * User of Password
     *
     * @param string $password
     *
     * @return self
     */
    public function withPassword($password)
    {
        $clone = clone $this;
        $clone->password = $password;

        return $clone;
    }

    /**
     * User of Status
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
     * Get User Registration Profile
     *
     * @return array
     */
    public function getRegistration()
    {
        return [
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'password' => $this->getPassword(),
            'status' => $this->getStatus() ?: self::STATUS_PENDING
        ];
    }

    /**
     * Get User Parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getEmail(),
            'status' => $this->getStatus()
        ];
    }

    /**
     * Format User Parameters
     *
     * @return array
     */
    public function formatParameters(&$params)
    {
        $params['id'] = $this->getId();
        $params['email'] = $this->getEmail();
        $params['status'] = $this->getStatus();
    }

    /**
     * Parse User Status
     *
     * @param string $status
     *
     * @return void
     */
    public static function parseStatus(&$status)
    {
        if ( ! in_array($status, self::$statuses)) {
            throw new InvalidArgumentException(sprintf('Invalid user status: %s', $status));
        }
    }

    /**
     * Parse User Criteria
     *
     * @param array $criteria
     *
     * @return void
     */
    public static function parseCriteria(array &$criteria = null)
    {
        $whitelist = ['email', 'name', 'status'];

        foreach ($criteria as $key => $value) {
            if ( ! in_array($key, $whitelist)) {
                throw new UnexpectedValueException(sprintf('Invalid user criterion: %s', $key));
            }
        }
    }

    /**
     * Create User from API Response
     *
     * @param array       $content Result from API request
     * @param Client|null $client  API Client
     *
     * @return self
     */
    public static function parse(array $content, Client $client = null)
    {
        $user = null;

        if (isset($content['id'])) {
            $user = new self($content);
            if ($client) $user = $user->withClient($client);
        }

        return $user;
    }

    /**
     * Authenticate User
     *
     * @param string $email    Users email address.
     * @param string $password Users password.
     *
     * @return bool
     */
    public function authenticate()
    {
        $data = [
            'email' => $this->getEmail(),
            'password' => $this->getPassword()
        ];

        $response = $this->request('POST', 'users/authenticate', $data);

        if ( ! empty($response['content'])) $this->setParameters($response['content']);

        return ($response['reasonPhrase'] === 'OK');
    }

    /**
     * Update User
     *
     * Permitted fields: name, email, status, password.
     *
     * @param array $profile User parameters.
     *
     * @return bool
     */
    public function update(array $profile)
    {
        return $this->getClient()->updateUser($this->getId(), $profile);
    }

    /**
     * Update User Profile
     *
     * Limit changes to fields: name, email.
     *
     * @param array $profile User profile parameters.
     *
     * @return bool
     */
    public function updateProfile(array $profile)
    {
        return $this->getClient()->updateUserProfile($this->getId(), $profile);
    }

    /**
     * Update User Password
     *
     * @param string $status Set status.
     *
     * @return bool
     */
    public function updatePassword($password)
    {
        return $this->getClient()->updateUserPassword($this->getId(), $password);
    }

    /**
     * Update User Status
     *
     * @param string $status Set status.
     *
     * @return bool
     */
    public function updateStatus($status)
    {
        return $this->getClient()->updateUserStatus($this->getId(), $status);
    }

    /**
     * Delete User
     *
     * @param int $force Whether to delete the row or not.
     *
     * @return bool
     */
    public function delete($force = 0)
    {
        $response = $this->request('DELETE', sprintf('users/%s?force=%d', $this->getId(), $force));

        return ($response['reasonPhrase'] === 'Accepted');
    }

    /**
     * Set User to Active
     *
     * @return bool
     */
    public function activate()
    {
        return $this->updateStatus(self::STATUS_ACTIVE);
    }

    /**
     * Get Application of User
     *
     * @return array
     */
    public function getApplications()
    {
        return $this->getClient()->getApplicationsByUser($this->getId());
    }

    /**
     * Get Application Persona
     *
     * @param string $applicationId Application ID
     *
     * @return Persona
     */
    public function getPersona($applicationId)
    {
        return $this->getClient()->getPersonaByApplicationUser($this->getId(), $applicationId);
    }
}
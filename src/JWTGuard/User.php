<?php

namespace RBennett\JWTGuard;

use \Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use RBennett\JWTGuard\Contracts\HasScopes;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User implements AuthenticatableContract, HasScopes, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * @var array
     */
    private $properties = array();

    /**
     * @var string
     */
    private $primaryKey = 'id';

    /**
     * @var null
     */
    private $password = null;

    /**
     * User constructor.
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->properties = $properties;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->properties[$name]);
    }

    /**
     * @param $name
     */
    public function __unset($name)
    {
        unset($this->properties[$name]);
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function tokenCan(string $scope)
    {
        return array_key_exists($scope, array_flip($this->scopes));
    }

}
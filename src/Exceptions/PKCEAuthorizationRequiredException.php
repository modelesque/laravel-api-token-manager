<?php

namespace Modelesque\ApiTokenManager\Exceptions;

use Exception;

class PKCEAuthorizationRequiredException extends Exception
{
    public function __construct($message = 'PKCE authorization is required.')
    {
        parent::__construct($message);
    }
}
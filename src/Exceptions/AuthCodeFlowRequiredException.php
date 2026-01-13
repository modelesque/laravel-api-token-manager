<?php

namespace Modelesque\ApiTokenManager\Exceptions;

use Exception;
use JetBrains\PhpStorm\Pure;

class AuthCodeFlowRequiredException extends Exception
{
    #[Pure]
    public function __construct($message = 'PKCE authorization is required.')
    {
        parent::__construct($message);
    }
}
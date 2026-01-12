<?php

namespace Modelesque\ApiTokenManager\Abstracts;

use Modelesque\ApiTokenManager\Factories\ApiClientFactory;

abstract class BaseClient
{
    public function __construct(
        protected ApiClientFactory $factory
    ) {}
}
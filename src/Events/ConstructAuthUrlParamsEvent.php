<?php

namespace Modelesque\ApiTokenManager\Events;

use Modelesque\ApiTokenManager\Services\Providers\AuthCodeTokenProvider;

/**
 * Allow API packages to add additional params to the authorization URL or modify the
 * standard ones that get added.
 *
 * @see AuthCodeTokenProvider::authorizeUrlParams()
 */
class ConstructAuthUrlParamsEvent
{
    /** @noinspection PhpParameterByRefIsNotUsedAsReferenceInspection */
    public function __construct(
        public array &$params,
        public string $provider,
        public string $account
    ) {}
}
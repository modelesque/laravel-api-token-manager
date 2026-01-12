<?php

namespace Modelesque\ApiTokenManager\Abstracts;

use Modelesque\ApiTokenManager\Enums\ApiAccount;
use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Factories\ApiClientFactory;

abstract class BaseClient
{
    public function __construct(
        protected ApiClientFactory $factory,
        protected string $configKey,
        protected string $account = '',
        protected string $grantType = ''
    ) {
        if (! $this->account) {
            $this->account = config("apis.providers.$configKey.default_account") ?? ApiAccount::PUBLIC->value;
        }

        if (! $this->grantType) {
            $this->grantType = config("apis.providers.$configKey.default_grant_type") ?? ApiTokenGrantType::PKCE->value;
        }
    }
}
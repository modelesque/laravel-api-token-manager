<?php

namespace Modelesque\ApiTokenManager\Abstracts;

use Modelesque\ApiTokenManager\Enums\ApiAccount;
use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Exceptions\InvalidConfigException;
use Modelesque\ApiTokenManager\Factories\ApiClientFactory;
use Modelesque\ApiTokenManager\Helpers\Config;

abstract class BaseClient
{
    /**
     * @throws InvalidConfigException
     */
    public function __construct(
        protected ApiClientFactory $factory,
        protected string $configKey,
        protected string $account = '',
        protected string $grantType = ''
    ) {
        if (! $this->account) {
            $this->account = Config::get(
                $this->configKey,
                'default_account',
                $this->account,
                ApiAccount::PUBLIC->value
            );
        }

        if (! $this->grantType) {
            $this->grantType = Config::get(
                $this->configKey,
                'default_grant_type',
                $this->account,
                ApiTokenGrantType::AUTHORIZATION_CODE->value
            );
        }
    }
}
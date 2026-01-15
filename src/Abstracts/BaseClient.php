<?php

namespace Modelesque\ApiTokenManager\Abstracts;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
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

    public function getConfigKey(): string
    {
        return $this->configKey;
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    public function getGrantType(): string
    {
        return $this->grantType;
    }

    #[Pure]
    #[ArrayShape(['configKey' => "string", 'account' => "string", 'grantType' => "string"])]
    public function debug(): array
    {
        return [
            'configKey' => $this->getConfigKey(),
            'account' => $this->getAccount(),
            'grantType' => $this->getGrantType(),
        ];
    }
}
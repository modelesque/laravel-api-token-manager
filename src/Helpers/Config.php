<?php

namespace Modelesque\ApiTokenManager\Helpers;

use Modelesque\ApiTokenManager\Exceptions\InvalidConfigException;

class Config
{
    /**
     * Return the API provider's full config array.
     *
     * @param string $provider
     * @return array
     * @throws InvalidConfigException
     */
    public static function getProvider(string $provider): array
    {
        $config = \config("apis.providers.$provider");
        if ($config === null) {
            throw new InvalidConfigException(sprintf(
                "Provider '%s' not found in 'providers' array in %s",
                $provider,
                config_path('apis.php')
            ));
        }

        if (! is_array($config)) {
            throw new InvalidConfigException(sprintf(
                "Provider '%s' in in %s must return an array.",
                $provider,
                config_path('apis.php')
            ));
        }

        return $config;
    }

    /**
     * Return a value in the "/config/apis.php" array for the API provider.
     *
     * @param string $provider
     * @param string $key
     * @param string $sub
     * @param null $default
     * @return mixed
     * @throws InvalidConfigException
     */
    public static function get(string $provider, string $key = '', string $sub = '', $default = null): mixed
    {
        $config = self::getProvider($provider);

        return $config[$key] ?? $config[$sub][$key] ?? $default;
    }

    /**
     * Return a required value in the "/config/apis.php" array for the API provider.
     *
     * @param string $provider
     * @param string $key
     * @param string $sub
     * @return mixed
     * @throws InvalidConfigException
     */
    public static function getRequired(string $provider, string $key, string $sub = ''): mixed
    {
        $value = self::get($provider, $key, $sub);

        if ($value === null) {
            throw new InvalidConfigException(sprintf(
                "Missing required%s config setting '%s' in %s",
                $provider ? " $provider" : '',
                $key,
                config_path('apis.php')
            ));
        }

        return $value;
    }
}
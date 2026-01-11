<?php

namespace Modelesque\ApiTokenManager\Abstracts;

use Modelesque\ApiTokenManager\Enums\ApiAccount;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

abstract class AuthTokenProvider
{
    protected array $config;

    public function __construct(
        public string $configKey,
        public string $account = '',
        protected int $retryAttempts = 2,
        protected int $retrySleepMs = 150
    ) {
        $this->config = config("apis.providers." . $this->configKey, []);
        if (! $this->account) {
            $this->account = ApiAccount::PUBLIC->value;
        }
    }

    /** @return string A read-friendly name of the API provider (e.g. "Spotify"). */
    protected function name(): string
    {
        return $this->configFind('name') ?? $this->throwConfigException('name');
    }

    /** @return string The API's URL to request an auth token. */
    protected function tokenUrl(): string
    {
        return $this->configFind('token_url') ?? $this->throwConfigException('token_url');
    }

    /** @return string The "client_id" the API gives you for authentication. */
    protected function clientId(): string
    {
        return $this->configFind('client_id') ?? '';
    }

    /** @return string The "client_secret" the API gives you for authentication. */
    protected function clientSecret(): string
    {
        return $this->configFind('client_secret') ?? '';
    }

    /** @return string The user ID for your account (not all APIs require this). */
    protected function userId(): string
    {
        return $this->configFind('user_id') ?? '';
    }

    /** @inheritdoc */
    public function sessionKey(): string
    {
        return implode('_', [
            'auth_state',
            $this->configKey,
            $this->account,
        ]);
    }

    /**
     * Return a value from the config.
     *
     * @param string $key
     * @return mixed
     */
    protected function configFind(string $key): mixed
    {
        return $this->config[$key] ?? $this->config[$this->account][$key] ?? null;
    }

    /** Helper to handle when required keys aren't present in the config. */
    protected function throwConfigException(string $key): void
    {
        $api = $this->configFind('name');
        throw new RuntimeException(sprintf(
            "Missing required%s config setting '%s' in %s",
            $api ? " $api" : '',
            $key,
            config_path('apis.php')
        ));
    }

    /**
     * Make the POST request to the API's token provider for a token.
     *
     * @param array $bodyParams
     * @param array $headers
     * @return array
     * @throws ConnectionException
     */
    protected function postRequestForToken(array $bodyParams, array $headers = []): array
    {
        $bodyParams = array_filter(
            $bodyParams,
            static fn($value) => $value !== null && $value !== ''
        );

        /** @var Response $response */
        $response = Http::retry($this->retryAttempts, $this->retrySleepMs)
            ->withHeaders($headers)
            ->asForm()
            ->post($this->tokenUrl(), $bodyParams);

        if ($response->successful()) {
            $json = $response->json();

            if (! is_array($json) || ! (isset($json['access_token']) || isset($json['token_type']))) {
                throw new RuntimeException("Token endpoint returned unexpected payload: " . $response->body());
            }

            return $json;
        }

        // If client error (4xx) don't retry further
        if ($response->status() >= 400 && $response->status() < 500) {
            throw new RuntimeException("Token endpoint responded with {$response->status()}: " . $response->body());
        }

        // For 5xx, the Http::retry above will already attempt retries; if we arrive here,
        // throw and let higher-level flow decide.
        throw new RuntimeException("Token endpoint failed: HTTP {$response->status()} " . $response->body());
    }

    /**
     * When receiving a response for a token, convert the 'expires_in' value to a
     * Carbon datetime object.
     *
     * @param mixed $value
     * @return Carbon|null
     */
    protected function normalizeValueForExpiresIn(mixed $value): ?Carbon
    {
        if (is_numeric($value)) {
            return Carbon::now()->addSeconds((int)$value);
        }

        // Some providers return an absolute expires_in timestamp (ISO8601 or epoch)
        try {
            return Carbon::parse($value);
        }
        catch (Throwable) {}

        return null;
    }
}
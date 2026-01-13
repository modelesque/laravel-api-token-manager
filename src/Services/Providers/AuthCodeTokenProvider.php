<?php

namespace Modelesque\ApiTokenManager\Services\Providers;

use Exception;
use Modelesque\ApiTokenManager\Abstracts\BaseTokenProvider;
use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Contracts\OAuth2TokenProviderInterface;
use Modelesque\ApiTokenManager\Contracts\AuthCodeTokenProviderInterface;
use Modelesque\ApiTokenManager\Contracts\PKCETokenProviderInterface;
use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Exceptions\InvalidConfigException;
use Modelesque\ApiTokenManager\Exceptions\AuthCodeFlowRequiredException;
use Modelesque\ApiTokenManager\Helpers\Config;
use Modelesque\ApiTokenManager\Models\ApiToken;
use Modelesque\ApiTokenManager\Providers\ApiClientServiceProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use InvalidArgumentException;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class AuthCodeTokenProvider extends BaseTokenProvider implements OAuth2TokenProviderInterface,AuthCodeTokenProviderInterface
{
    /** @var bool */
    public bool $usesPkce = false;

    /** @var string The code obtained from the auth redirect. */
    public string $code = '';

    /** @var string The URL you provided when authenticating. */
    protected string $redirectUrl = '';

    /** @var string The string you provided when authenticating. */
    protected string $codeVerifier = '';

    public function __construct(
        string $configKey,
        string $account,
        int $retryAttempts = 2,
        int $retrySleepMs = 150,
        string $redirectUri = ''
    )
    {
        parent::__construct($configKey, $account, $retryAttempts, $retrySleepMs);
        $this->setRedirectUrl($redirectUri);
        $this->usesPkce = (bool)Config::get($configKey, 'uses_pkce', $account, false);
    }

    /** @inheritdoc */
    public function requestToken(?ApiToken $token): array
    {
        /**
         * If returning to the redirect URL from the API's auth page, these properties
         * will get set when this class instantiates, so now a token can be requested.
         * @see ApiClientServiceProvider::boot()
         */
        if ($this->code && $this->redirectUrl && $this->codeVerifier) {
            return $this->postForNewToken();
        }

        // if a refresh token was previously saved, use it to request a new token
        if ($token?->refresh_token) {
            return $this->postToRefreshExpiredToken($token->refresh_token);
        }

        // authorization is required on the API's site before requesting a fresh token
        throw new AuthCodeFlowRequiredException();
    }

    /**
     * After returning from the API's authorization interface, use the `$authCode` they
     * provide, along with the `$redirectUrl` and `$codeVerifier` used prior to reaching
     * their interface, to make a POST request for the new token.
     *
     * @return array
     * @throws ConnectionException
     * @throws InvalidConfigException
     */
    protected function postForNewToken(): array
    {
        $response = $this->postRequestForToken(array_filter([
            'grant_type' => ApiTokenGrantType::AUTHORIZATION_CODE->value,
            'client_id' => $this->clientId(),
            'client_secret' => $this->usesPkce ? false : $this->clientSecret(),
            'code' => $this->code,
            'redirect_uri' => $this->getRedirectUrl(),

            // needed for PKCE
            'code_verifier' => $this->getCodeVerifier(),
        ]));

        return $this->normalizeResponse($response);
    }

    /**
     * Request a new auth token using a saved refresh token.
     *
     * @param string $refreshToken
     * @return array
     * @throws ConnectionException
     * @throws InvalidConfigException
     */
    protected function postToRefreshExpiredToken(string $refreshToken): array
    {
        $response = $this->postRequestForToken(array_filter([
            'grant_type' => ApiTokenGrantType::REFRESH_TOKEN->value,
            'client_id' => $this->clientId() ?: false,
            'refresh_token' => $refreshToken,
        ]));

        return $this->normalizeResponse($response);
    }

    /**
     * After requesting a token from the API, normalize its response so the
     * data can be saved as an ApiToken.
     *
     * @param array $response
     * @return array
     */
    protected function normalizeResponse(array $response): array
    {
        $token = $response['access_token'] ?? $response['token'] ?? false;
        $refreshToken = $response['refresh_token'] ?? $response['refresh'] ?? false;
        $tokenType = strtolower($response['token_type'] ?? 'bearer');
        $scope = $response['scope'] ?? $response['scopes'] ?? false;
        $expiresAt = $this->normalizeValueForExpiresIn(
            $response['expires_in'] ?? $response['expires_at'] ?? $response['expires'] ?? null
        );

        return array_filter([
            'token_type' => $tokenType,
            'token' => $token,
            'refresh_token' => $refreshToken,
            'scope' => $scope,
            'expires_at' => $expiresAt ?: false,
            'meta' => $response,
        ]);
    }

    /** @return string The code verifier for the PKCE process */
    public function getCodeVerifier(): string
    {
        if ($this->codeVerifier) {
            return $this->codeVerifier;
        }

        try {
            $verifierBytes = random_bytes(64);
            $this->setCodeVerifier(
                rtrim(
                    strtr(
                        base64_encode($verifierBytes),
                        "+/",
                        "-_"
                    ), "="
                )
            );
        }
        catch (Exception) {}

        return $this->codeVerifier;
    }

    /** @param string|mixed $codeVerifier Store the code verifier for reuse during the PKCE process */
    public function setCodeVerifier(mixed $codeVerifier): void
    {
        if (is_string($codeVerifier)) {
            $this->codeVerifier = $codeVerifier;
        }
    }

    /** @return string The code challenge for the PKCE process */
    public function getCodeChallenge(string $codeVerifier): string
    {
        $challengeBytes = hash("sha256", $codeVerifier, true);

        return rtrim(strtr(base64_encode($challengeBytes), "+/", "-_"), "=");
    }

    /** @param string|mixed $uri */
    public function setRedirectUrl(mixed $uri): void
    {
        if ($uri && is_string($uri)) {
            $segments = Uri::of($uri)->pathSegments();
            $this->redirectUrl = url()->to($segments->implode('/'));
        }
    }

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function getRedirectUrl(): string
    {
        if (! $this->redirectUrl) {
            $this->setRedirectUrl(
                Config::getRequired($this->configKey, 'redirect_uri', $this->account)
            );
        }

        return $this->redirectUrl;
    }

    /**
     * The query params that will be added to the query string when requesting an auth
     * code from an API during the PKCE process.
     *
     * @param string $state
     * @return array
     * @throws InvalidConfigException
     * @see authorizeUrl
     */
    #[ArrayShape([
        'response_type' => "string",
        'client_id' => "string",
        'redirect_uri' => "string",
        'scope' => "string",
        'code_challenge_method' => "string",
        'code_challenge' => "string",
        'state' => "string"
    ])]
    public function authorizeUrlParams(string $state = ''): array
    {
        $scope = Config::get($this->configKey, 'scope', $this->account, []);

        return array_filter([
            'client_id' => $this->clientId(),
            'redirect_uri' => $this->getRedirectUrl(),
            'response_type' => 'code',
            'scope' => is_array($scope) ? implode(' ', $scope) : false,
            'state' => $state,

            // needed for PKCE
            'code_challenge_method' => $this->usesPkce ? 'S256' : false,
            'code_challenge' => $this->usesPkce
                ? $this->getCodeChallenge($this->getCodeVerifier())
                : false,
        ]);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function authorize(): RedirectResponse
    {
        /** @var Request $request */
        $request = request();

        // the key we'll use to validate upon returning from the auth page
        $state = Str::random(40);

        /**
         * Store the session variables for the return from the API's auth page.
         *
         * There are 3 URLs to keep track of:
         *
         * `actionUrl`   - The URL that called the controller action that needs this
         *                 authorization. This only applies to GET requests.
         * `referrerUrl` - The URL that triggered the `actionUrl` (e.g. the page
         *                 with a submit button on it). This is needed for error
         *                 handling and POST requests.
         * `redirectUrl` - The URL that the external API (e.g. Spotify) knows about in
         *                 your Developer account's API settings and uses as another
         *                 form of security checking. The API will redirect you back to
         *                 this URL whether authorization succeeds or fails.
         */
        $request->session()->put($this->sessionKey(), [
            'actionUrl' => $request->fullUrl(),
            'codeVerifier' => $this->getCodeVerifier(),
            'wasPost' => $request->method() === 'POST',
            'redirectUrl' => $this->getRedirectUrl(),
            'referrerUrl' => $request->headers->get('referrer'),
            'state' => $state,
        ]);

        // construct the url to the API's auth page
        $url = Config::getRequired($this->configKey, 'base_auth_url', $this->account);
        $params = $this->authorizeUrlParams($state);

        return redirect()->away($url . '?' . http_build_query($params));
    }

    /** @inheritdoc */
    public function handlePostAuthorization(): RedirectResponse
    {
        /** @var Request $request */
        $request = request();

        // unpack the session variables
        $sessionVars = $request->session()->get($this->sessionKey());
        [
            'actionUrl' => $actionUrl,
            'codeVerifier' => $codeVerifier,
            'wasPost' => $wasPost,
            'redirectUrl' => $redirectUrl,
            'referrerUrl' => $referrerUrl,
            'state' => $state,
        ] = $sessionVars;

        // the API could have had an error on their end
        if ($error = $request->query('error')) {

            // note: not all APIs provide 'error_description'
            if ($description = $request->query('error_description', '')) {
                $error .= ': ' . $description;
            }

            $error = sprintf(
                "Error getting auth code from %s during PKCE process: %s",
                $this->name(),
                $error
            );

            Log::error($error);

            return redirect($referrerUrl)->with('error', $error);
        }

        // validate the query params 'state' and 'code'
        if (! $state || ! hash_equals($state, $request->query('state', ''))) {
            throw new InvalidArgumentException('Invalid state parameter.');
        }

        $this->code = $request->query('code');
        if (! $this->code) {
            throw new InvalidArgumentException('Invalid code parameter');
        }

        // these, along with 'code', are required params for fetching the new auth token
        $this->setCodeVerifier($codeVerifier);
        $this->setRedirectUrl($redirectUrl);

        // post for the new token and save it to the db
        try {
            $payload = $this->postForNewToken();
            $payload['grant_type'] = ApiTokenGrantType::AUTHORIZATION_CODE->value;
            app()->make(ApiTokenRepositoryInterface::class)
                ->saveToken(
                    $this->configKey,
                    $this->account,
                    ApiTokenGrantType::AUTHORIZATION_CODE->value,
                    $payload
                );
            $message = sprintf("%s access token saved.", $this->name());
        }
        catch (Throwable $exception) {
            $error = sprintf("Error requesting new API auth token from %s", $this->name());
            Log::error($error, ['exception' => $exception]);

            return redirect($referrerUrl)->with('error', $error);
        }

        // invalidate the session so the request can't be reproduced
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // if the original request was POST, then we can't continue on with the original
        // action because CSRF etc. isn't secure to keep track of. Instead, return to
        // the page that initiated the POST request and resubmit the form now that a
        // token exists.
        if ($wasPost) {
            return redirect($referrerUrl)->with('success', $message . ' Re-submit form.');
        }

        // run original GET action
        return redirect($actionUrl)->with('success', $message);
    }
}
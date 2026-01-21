<?php /** @noinspection StaticClosureCanBeUsedInspection */

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Modelesque\ApiTokenManager\Contracts\ApiTokenRepositoryInterface;
use Modelesque\ApiTokenManager\Contracts\OAuth2TokenProviderInterface;
use Modelesque\ApiTokenManager\Enums\ApiAccount;
use Modelesque\ApiTokenManager\Enums\ApiTokenGrantType;
use Modelesque\ApiTokenManager\Models\ApiToken;
use Modelesque\ApiTokenManager\Services\Providers\AuthCodeTokenProvider;
use Modelesque\ApiTokenManager\Services\Providers\ClientCredentialsTokenProvider;
use Modelesque\ApiTokenManager\Services\TokenManager;

$grantTypes = [
    ApiTokenGrantType::CLIENT_CREDENTIALS->label() => ApiTokenGrantType::CLIENT_CREDENTIALS->value,
    ApiTokenGrantType::AUTHORIZATION_CODE->label() => ApiTokenGrantType::AUTHORIZATION_CODE->value,
];

/**
 * Test the positive scenario where a saved token already exists in the db.
 * @see TokenManager::getToken()
 */
test('TokenManager::getToken() returns a saved, valid token', function (string $grantType) {
    $configKey = 'test';
    $account = ApiAccount::PUBLIC->value;
    $tokenValue = 'saved_token';

    // create a fake ApiTokenRepository that finds a saved token in the db
    $repo = Mockery::mock(ApiTokenRepositoryInterface::class);
    $repo->shouldReceive('getSavedToken')
        ->once()
        ->with($configKey, $account, $grantType)
        ->andReturn(ApiToken::make([
            'provider' => $configKey,
            'account' => $account,
            'grant_type' => $grantType,
            'token' => $tokenValue,
            'expires_at' => Carbon::now()->addHour(),
        ]));

    // since the token is found, there's never a need to run `saveToken()` later in the method
    $repo->shouldReceive('saveToken')->never();

    // run the unit test with everything in place
    $tokenManager = new TokenManager($repo);
    $result = $tokenManager->getToken($configKey, $account, $grantType);
    expect($result)->toBe($tokenValue);

})->with($grantTypes);


/**
 * Test the positive scenario where a new token must be requested because one doesn't exist in the db.
 * @see TokenManager::getToken()
 */
test("TokenManager::getToken() requests a new token from the provider and saves it in the database", function(string $grantType) {
    $configKey = 'test';
    $account = ApiAccount::PUBLIC->value;
    $tokenValue = 'new_token';

    // pretend an API config exists
    Config::set("apis.providers.$configKey", []);

    // create a fake ApiTokenRepository that searches for a token in the db and returns null
    $repo = Mockery::mock(ApiTokenRepositoryInterface::class);
    $repo->shouldReceive('getSavedToken')
        ->once()
        ->with($configKey, $account, $grantType)
        ->andReturnNull();

    // prep the provider and its payload as if it requested a new token from the API
    $providerPayload = [
        'expires_at' => Carbon::now()->addHour(),
        'grant_type' => $grantType,
        'provider' => $configKey,
        'token' => $tokenValue,
    ];
    $mockProvider = match($grantType) {
        ApiTokenGrantType::CLIENT_CREDENTIALS->value => Mockery::mock(ClientCredentialsTokenProvider::class),
        ApiTokenGrantType::AUTHORIZATION_CODE->value => Mockery::mock(AuthCodeTokenProvider::class),
        default => throw new RuntimeException('Invalid grant type provided.'),
    };
    $mockProvider->shouldReceive('requestToken')
        ->once()
        ->with(null)
        ->andReturn($providerPayload);

    // save the fake payload-token-data from the API to the db
    $repo->shouldReceive('saveToken')
        ->once()
        ->with(
            $configKey,
            $account,
            $grantType,
            Mockery::on(static fn($payload) =>
                is_array($payload) &&
                $payload['token'] === $providerPayload['token'] &&
                $payload['grant_type'] === $grantType
            )
        )
        ->andReturn(ApiToken::make([
            'provider' => $configKey,
            'account' => $account,
            'grant_type' => $grantType,
            'token' => $tokenValue,
            'expires_at' => Carbon::now()->addHour(),
        ]));

    // create a fake TokenManager to override makeProvider() and return the mock provider
    // created above.
    $manager = new class($repo, $mockProvider) extends TokenManager {
        private OAuth2TokenProviderInterface $testProvider;
        public function __construct($repo, $testProvider)
        {
            parent::__construct($repo); $this->testProvider = $testProvider;
        }
        public function makeProvider(string $configKey, string $account, string $grantType): OAuth2TokenProviderInterface
        {
            return $this->testProvider;
        }
    };

    // now run the unit test with everything in place
    $token = $manager->getToken($configKey, $account, $grantType);
    expect($token)->toBe($tokenValue);
})->with($grantTypes);
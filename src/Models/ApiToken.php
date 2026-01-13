<?php

namespace Modelesque\ApiTokenManager\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $account
 * @property Carbon $expires_at
 * @property string $grant_type
 * @property array $meta
 * @property string $provider
 * @property string $refresh_token
 * @property string $scope
 * @property string $token
 * @property string $token_type
 */
class ApiToken extends Model
{
    /** @inheritdoc */
    protected $fillable = [
        'account',
        'expires_at',
        'grant_type',
        'meta',
        'provider',
        'refresh_token',
        'scope',
        'token',
        'token_type',
    ];

    /** @inheritdoc */
    protected $casts = [
        'expires_at' => 'datetime',
        'meta' => 'array',
        'refresh_token' => 'encrypted',
        'token' => 'encrypted',
    ];
}
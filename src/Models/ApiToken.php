<?php

namespace Modelesque\ApiTokenManager\Models;

use Illuminate\Database\Eloquent\Model;

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
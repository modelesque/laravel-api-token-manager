<?php

namespace Modelesque\ApiTokenManager\Abstracts;

use Illuminate\Http\Client\PendingRequest;

abstract class BaseRequest
{
    public function __construct(protected PendingRequest $client) {}
}
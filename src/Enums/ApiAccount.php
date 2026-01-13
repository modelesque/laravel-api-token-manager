<?php

namespace Modelesque\ApiTokenManager\Enums;

enum ApiAccount: string
{
    case PRIVATE = 'private';
    case PUBLIC = 'public';

    public function label(): string
    {
        return match ($this) {
            self::PRIVATE => 'Private',
            self::PUBLIC => 'Public',
        };
    }
}
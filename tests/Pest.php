<?php

use Modelesque\ApiTokenManager\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

afterEach(function () {
    // Ensure Mockery expectations are verified and resources freed
    Mockery::close();
});
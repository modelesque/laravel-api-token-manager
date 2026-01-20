<?php

use Illuminate\Support\Facades\Schema;

it("creates an 'api_tokens' table", function() {
    expect(Schema::hasTable('api_tokens'))->toBeTrue();
});
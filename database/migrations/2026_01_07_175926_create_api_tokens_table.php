<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->index();
            $table->string('account')->default('public'); // public or private
            $table->string('grant_type')->nullable(); // 'authorization_code', 'client_credentials', etc.
            $table->string('token_type')->default('bearer'); // bearer, api-key, etc
            $table->text('token');
            $table->text('refresh_token')->nullable();
            $table->text('scope')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_tokens');
    }
};
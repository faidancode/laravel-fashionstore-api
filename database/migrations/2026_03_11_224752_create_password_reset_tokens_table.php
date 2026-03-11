<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $blueprint->string('token')->index();
            $blueprint->string('pin', 10)->nullable();
            $blueprint->timestamp('expires_at');
            $blueprint->timestamp('created_at')->nullable();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};

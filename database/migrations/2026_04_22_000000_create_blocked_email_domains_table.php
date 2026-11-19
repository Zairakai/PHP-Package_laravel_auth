<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function down(): void
    {
        Schema::dropIfExists('blocked_email_domains');
    }

    public function up(): void
    {
        Schema::create('blocked_email_domains', function (Blueprint $table): void {
            $table->id();
            // The domain as it was given
            $table->string('domain');
            // The domain in lowercase: the one that is compared
            $table->string('normalized_domain')->unique();
            // Why the domain is blocked: keys of the laravel-auth::indicators translations
            $table->json('indicators')->nullable();
        });
    }
};

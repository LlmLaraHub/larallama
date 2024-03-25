<?php

use App\Enums\ApiKeyTypeEnum;
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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('key')->unique();
            $table->string('name');
            $table->json('settings');

            $table->boolean('is_active')->default(false);

            $table->enum('type', ApiKeyTypeEnum::values())->default('openai');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};

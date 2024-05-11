<?php

use App\Models\Chat;
use App\Models\Collection;
use App\Models\Message;
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
        Schema::create('prompt_histories', function (Blueprint $table) {
            $table->id();
            $table->longText('prompt');
            $table->foreignIdFor(Chat::class);
            $table->foreignIdFor(Collection::class);
            $table->foreignIdFor(Message::class)->nullable(); //might not always lead to one
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prompt_histories');
    }
};

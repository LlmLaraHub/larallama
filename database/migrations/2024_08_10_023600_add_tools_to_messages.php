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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('tool_name')->nullable();
            $table->string('tool_id')->nullable();
            $table->string('driver')->nullable();
            $table->json('args')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('args');
            $table->dropColumn('tool_id');
            $table->dropColumn('tool_name');
            $table->dropColumn('driver');
        });
    }
};

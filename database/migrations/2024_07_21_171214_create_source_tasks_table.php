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
        Schema::create('source_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Source::class);
            $table->text('task_key');
            $table->timestamps();
        });

        Schema::table('source_tasks', function (Blueprint $table) {
            $table->index(['source_id', 'task_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_tasks');
    }
};

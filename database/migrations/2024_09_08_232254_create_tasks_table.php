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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('details');
            $table->date('completed_at')->nullable();
            $table->foreignIdFor(\App\Models\Project::class)->constrained();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('assistant')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

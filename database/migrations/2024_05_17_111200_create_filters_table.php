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
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Collection::class);
            $table->foreignIdFor(\App\Models\User::class, 'created_by_id');
            $table->string('name');
            $table->longText('description')->nullable();
            $table->timestamps();
        });

        Schema::create('collection_filter', function (Blueprint $table) {
            $table->foreignId('filter_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('collection_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filters');
    }
};

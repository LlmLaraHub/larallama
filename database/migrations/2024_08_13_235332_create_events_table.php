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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('end_date')->nullable();
            $table->time('end_time')->nullable();
            $table->string('location')->nullable();
            $table->boolean('assigned_to_assistant')->default(false);
            $table->boolean('all_day')->default(false);
            $table->string('type')
                ->default(\App\Domains\Events\EventTypes::Event->value);
            $table->foreignIdFor(\App\Models\User::class, 'assigned_to_id')->nullable();
            $table->foreignIdFor(\App\Models\Collection::class, 'collection_id')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

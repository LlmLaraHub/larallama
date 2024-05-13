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
        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Collection::class);
            $table->boolean('active')->default(false);
            $table->boolean('public')->default(false);
            $table->longText('summary')->nullable(); //going to let AI generate it
            $table->string('title');
            $table->json('meta_data')->nullable();
            $table->string('type')->default(\App\Domains\Outputs\OutputTypeEnum::WebPage->value);
            $table->string('slug')->nullable(); //will be made during save
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outputs');
    }
};

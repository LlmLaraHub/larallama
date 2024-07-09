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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->nullable();
            $table->longText('content')->nullable();
            $table->longText('response')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignIdFor(\App\Models\Report::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\Document::class)->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};

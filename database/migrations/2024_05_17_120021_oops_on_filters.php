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

        Schema::drop('collection_filter');

        Schema::create('document_filter', function (Blueprint $table) {
            $table->foreignId('filter_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('document_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

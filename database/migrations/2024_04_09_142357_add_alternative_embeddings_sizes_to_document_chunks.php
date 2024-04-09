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
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->vector('embedding_1536', 1536)->nullable();
            $table->vector('embedding_2048', 2048)->nullable();
            $table->vector('embedding_3072', 3072)->nullable();
            $table->vector('embedding_4096', 4096)->nullable();
            $table->dropColumn('embedding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_chunks', function (Blueprint $table) {
            //
        });
    }
};

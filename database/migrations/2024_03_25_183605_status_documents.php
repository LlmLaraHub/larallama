<?php

use App\Domains\Documents\StatusEnum;
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
            $table->string('status_embeddings')->default(StatusEnum::Pending);
            $table->string('status_tagging')->default(StatusEnum::Pending);
            $table->string('status_summary')->default(StatusEnum::Pending);
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

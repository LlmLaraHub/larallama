<?php

use App\Models\Chat;
use App\Models\Document;
use App\Models\DocumentChunk;
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
        Schema::create('chat_document_references', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Chat::class);
            $table->foreignIdFor(Document::class);
            $table->foreignIdFor(DocumentChunk::class);
            $table->string('reference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_document_references');
    }
};

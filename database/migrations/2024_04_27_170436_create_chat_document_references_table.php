<?php

use App\Models\DocumentChunk;
use App\Models\Message;
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
        Schema::create('message_document_references', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Message::class);
            $table->foreignIdFor(DocumentChunk::class);
            $table->decimal('distance', 18, 15)->nullable();
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

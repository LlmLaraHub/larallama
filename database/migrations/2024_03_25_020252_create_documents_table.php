<?php

use App\Domains\Documents\StatusEnum;
use App\Models\Collection;
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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('status')->default(StatusEnum::Pending);
            $table->longText('summary')->nullable();
            $table->string('file_path')->nullable();
            $table->foreignIdFor(Collection::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

<?php

use App\Domains\Sources\SourceTypeEnum;
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
        Schema::create('sources', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('details');
            $table->foreignIdFor(Collection::class);
            $table->string('type')->default(SourceTypeEnum::WebSearchSource->value);
            $table->string('recurring')->nullable();
            $table->datetime('last_run')->nullable();
            $table->json('meta_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sources');
    }
};

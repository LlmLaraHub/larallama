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
        Schema::create('transformers', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default(\App\Domains\Transformers\TypeEnum::GenericTransformer->value);
            $table->longText('details')->nullable();
            $table->morphs('transformable');
            $table->foreignIdFor(\App\Models\Transformer::class, 'parent_id')->nullable();
            $table->dateTime('last_run');
            $table->boolean('active')->default(1);
            $table->timestamps();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Transformer::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transformers');
    }
};

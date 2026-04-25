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
        Schema::create('project_states', function (Blueprint $table) {
            $table->id();
            $table->string('stateable_id');
            $table->string('stateable_type');
            $table->string('current_phase');
            $table->json('history')->nullable();
            $table->timestamps();
            
            $table->index(['stateable_id', 'stateable_type']);
        });

        Schema::create('unified_subfases', function (Blueprint $table) {
            $table->id();
            $table->string('faseable_id');
            $table->string('faseable_type');
            $table->string('subfase_key');
            $table->string('status')->default('waiting');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['faseable_id', 'faseable_type']);
            $table->unique(['faseable_id', 'faseable_type', 'subfase_key'], 'subfase_unique');
        });

        Schema::create('unified_evidences', function (Blueprint $table) {
            $table->id();
            $table->string('faseable_id');
            $table->string('faseable_type');
            $table->string('type');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_extension')->nullable();
            $table->string('file_size')->nullable();
            $table->timestamps();

            $table->index(['faseable_id', 'faseable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_states');
        Schema::dropIfExists('unified_subfases');
        Schema::dropIfExists('unified_evidences');
    }
};

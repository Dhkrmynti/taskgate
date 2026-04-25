<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_boq_details', function (Blueprint $table) {
            $table->id();
            $table->string('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->string('designator');
            $table->text('description');
            $table->unsignedInteger('volume_planning')->default(0);
            $table->decimal('price_planning', 15, 2)->default(0);
            $table->unsignedInteger('volume_pemenuhan')->default(0);
            $table->unsignedInteger('volume_aktual')->default(0);
            $table->decimal('price_aktual', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['project_id', 'sort_order']);
            $table->index('designator');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_boq_details');
    }
};

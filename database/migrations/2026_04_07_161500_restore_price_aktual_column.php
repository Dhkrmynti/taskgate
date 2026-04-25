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
        Schema::table('project_boq_details', function (Blueprint $table) {
            $table->decimal('price_aktual', 15, 2)->default(0)->after('volume_aktual');
        });

        // Initialize price_aktual with price_planning for existing records
        \Illuminate\Support\Facades\DB::table('project_boq_details')
            ->update(['price_aktual' => \Illuminate\Support\Facades\DB::raw('price_planning')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_boq_details', function (Blueprint $table) {
            $table->dropColumn('price_aktual');
        });
    }
};

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
        Schema::table('warehouse_rekons', function (Blueprint $table) {
            $table->string('finance_rekon_id')->nullable()->after('fase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_rekons', function (Blueprint $table) {
            $table->dropColumn('finance_rekon_id');
        });
    }
};

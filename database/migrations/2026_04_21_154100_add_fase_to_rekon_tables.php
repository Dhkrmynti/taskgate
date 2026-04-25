<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commerce_rekons', function (Blueprint $table) {
            $table->string('fase')->default('ogp_rekon_ba')->after('id');
        });
        Schema::table('warehouse_rekons', function (Blueprint $table) {
            $table->string('fase')->default('ogp_rekon_wh')->after('id');
        });
        Schema::table('finance_rekons', function (Blueprint $table) {
            $table->string('fase')->default('ogp_finance_rf')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('commerce_rekons', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
        Schema::table('warehouse_rekons', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
        Schema::table('finance_rekons', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
    }
};

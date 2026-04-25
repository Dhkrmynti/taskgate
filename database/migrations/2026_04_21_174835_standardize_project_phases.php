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
        $tables = ['projects', 'project_batches', 'commerce_rekons', 'warehouse_rekons'];

        $mapping = [
            'start' => 'planning',
            'ogp_procurement_po' => 'procurement',
            'ogp_procurement' => 'procurement',
            'ogp_konstruksi' => 'konstruksi',
            'ogp_rekon' => 'rekon',
            'ogp_rekon_ba' => 'rekon',
            'ogp_commerce' => 'rekon',
            'ogp_warehouse' => 'warehouse',
            'ogp_rekon_wh' => 'warehouse',
            'ogp_finance' => 'finance',
            'close' => 'closed',
        ];

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                foreach ($mapping as $old => $new) {
                    \Illuminate\Support\Facades\DB::table($table)
                        ->where('fase', $old)
                        ->update(['fase' => $new]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No easy way to reverse without potentially overlapping states, 
        // but typically standardizing phase isn't reversed.
    }
};

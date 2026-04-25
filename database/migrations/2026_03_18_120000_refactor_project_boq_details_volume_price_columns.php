<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_boq_details', function (Blueprint $table) {
            if (!Schema::hasColumn('project_boq_details', 'volume_planning')) {
                $table->unsignedInteger('volume_planning')->default(0)->after('description');
            }
            if (!Schema::hasColumn('project_boq_details', 'price_planning')) {
                $table->decimal('price_planning', 15, 2)->default(0)->after('volume_planning');
            }
            if (!Schema::hasColumn('project_boq_details', 'volume_pemenuhan')) {
                $table->unsignedInteger('volume_pemenuhan')->default(0)->after('price_planning');
            }
            if (!Schema::hasColumn('project_boq_details', 'price_pemenuhan')) {
                $table->decimal('price_pemenuhan', 15, 2)->default(0)->after('volume_pemenuhan');
            }
            if (!Schema::hasColumn('project_boq_details', 'volume_aktual')) {
                $table->unsignedInteger('volume_aktual')->default(0)->after('price_pemenuhan');
            }
            if (!Schema::hasColumn('project_boq_details', 'price_aktual')) {
                $table->decimal('price_aktual', 15, 2)->default(0)->after('volume_aktual');
            }
        });

        if (Schema::hasColumn('project_boq_details', 'volume')) {
            DB::table('project_boq_details')->update([
                'volume_planning' => DB::raw('volume'),
                'price_planning' => DB::raw('price'),
            ]);
        }

        Schema::table('project_boq_details', function (Blueprint $table) {
            foreach (['volume', 'price', 'subtotal'] as $col) {
                if (Schema::hasColumn('project_boq_details', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('project_boq_details', function (Blueprint $table) {
            $table->unsignedInteger('volume')->default(0)->after('description');
            $table->decimal('price', 15, 2)->default(0)->after('volume');
            $table->decimal('subtotal', 15, 2)->default(0)->after('price');
        });

        DB::table('project_boq_details')->update([
            'volume' => DB::raw('volume_planning'),
            'price' => DB::raw('price_planning'),
            'subtotal' => DB::raw('volume_planning * price_planning'),
        ]);

        Schema::table('project_boq_details', function (Blueprint $table) {
            $table->dropColumn([
                'volume_planning',
                'price_planning',
                'volume_pemenuhan',
                'price_pemenuhan',
                'volume_aktual',
                'price_aktual',
            ]);
        });
    }
};

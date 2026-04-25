<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('fase')->default('start')->after('customer');
            $table->index('fase');
        });

        DB::table('projects')
            ->whereNull('fase')
            ->update(['fase' => 'start']);
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['fase']);
            $table->dropColumn('fase');
        });
    }
};

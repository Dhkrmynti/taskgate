<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE projects MODIFY fase VARCHAR(50) NOT NULL DEFAULT 'start'");
        }

        DB::table('projects')->where('fase', 'on progress')->update(['fase' => 'ogp_konstruksi']);
        DB::table('projects')->where('fase', 'verifikasi')->update(['fase' => 'ogp_commerce']);
        DB::table('projects')->whereNull('fase')->update(['fase' => 'start']);
    }

    public function down(): void
    {
        DB::table('projects')->where('fase', 'ogp_konstruksi')->update(['fase' => 'on progress']);
        DB::table('projects')->where('fase', 'ogp_commerce')->update(['fase' => 'verifikasi']);
        DB::table('projects')->where('fase', 'pembayaran_mitra')->update(['fase' => 'verifikasi']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE projects MODIFY fase VARCHAR(50) NOT NULL DEFAULT 'start'");
        }
    }
};

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune {--days=180 : The number of days of logs to keep}';

    protected $description = 'Hapus log aktivitas yang sudah terlalu lama untuk menjaga performa database';

    public function handle(): void
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Menghapus activity logs yang dibuat sebelum {$cutoff->toDateTimeString()}...");

        $count = \App\Models\ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Berhasil menghapus {$count} baris log lama.");
        
        // Optimize table (MySQL specific)
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('OPTIMIZE TABLE activity_logs');
            $this->info("Tabel activity_logs telah di-optimize.");
        }
    }
}

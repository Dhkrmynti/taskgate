<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'customers',
            'portofolios',
            'programs',
            'execution_types',
            'witels',
            'pm_projects',
            'waspangs',
        ];

        foreach ($tables as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'waspangs',
            'pm_projects',
            'witels',
            'execution_types',
            'programs',
            'portofolios',
            'customers',
        ];

        foreach ($tables as $tableName) {
            Schema::dropIfExists($tableName);
        }
    }
};

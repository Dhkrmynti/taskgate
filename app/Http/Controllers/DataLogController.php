<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class DataLogController extends Controller
{
    public function index(string $module): View
    {
        $config = $this->getModuleConfig($module);
        
        return view('data-log', [
            'module' => $module,
            'title' => $config['title'],
            'role' => $config['role'],
        ]);
    }

    public function data(string $module): JsonResponse
    {
        $query = ActivityLog::with('user')
            ->where('module', $module)
            ->latest('id');

        return DataTables::eloquent($query)
            ->editColumn('created_at', fn($log) => $log->created_at->format('Y-m-d H:i') ?: '-')
            ->addColumn('user_name', fn($log) => $log->user?->name ?: 'System')
            ->editColumn('action', fn($log) => mb_strtoupper($log->action))
            ->make(true);
    }

    private function getModuleConfig(string $module): array
    {
        return match ($module) {
            'commerce' => [
                'title' => 'Commerce Data Log',
                'role' => 'commerce',
            ],
            'finance' => [
                'title' => 'Finance Data Log',
                'role' => 'finance',
            ],
            'procurement' => [
                'title' => 'Procurement Data Log',
                'role' => 'procurement',
            ],
            'warehouse' => [
                'title' => 'Warehouse Data Log',
                'role' => 'warehouse',
            ],
            default => abort(404),
        };
    }
}

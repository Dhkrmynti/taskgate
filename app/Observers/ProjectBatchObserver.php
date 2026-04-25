<?php

namespace App\Observers;

use App\Models\ProjectBatch;
use App\Models\User;
use App\Notifications\ProjectPhaseReminder;
use Illuminate\Support\Facades\Notification;

class ProjectBatchObserver
{
    /**
     * Handle the ProjectBatch "created" event.
     */
    public function created(ProjectBatch $batch): void
    {
        $this->notifyRoleForPhase($batch);
    }

    /**
     * Handle the ProjectBatch "updated" event.
     */
    public function updated(ProjectBatch $batch): void
    {
        if ($batch->isDirty('fase')) {
            $this->notifyRoleForPhase($batch);
        }
    }

    /**
     * Notify the role responsible for the current batch phase.
     */
    protected function notifyRoleForPhase(ProjectBatch $batch): void
    {
        $phaseRoleMap = [
            ProjectBatch::PHASE_PROCUREMENT => 'konstruksi',
            ProjectBatch::PHASE_KONSTRUKSI => 'commerce',
            ProjectBatch::PHASE_REKON => 'warehouse',
            ProjectBatch::PHASE_WAREHOUSE => 'finance',
            ProjectBatch::PHASE_FINANCE => 'finance', 
        ];

        $targetRole = $phaseRoleMap[$batch->fase] ?? null;

        if ($targetRole) {
            $users = User::where('role', $targetRole)->get();
            
            if ($users->isNotEmpty()) {
                $message = $this->getNotificationMessage($batch->fase);
                Notification::send($users, new ProjectPhaseReminder($batch, $message));
            }
        }
    }

    protected function getNotificationMessage(string $phase): string
    {
        return match ($phase) {
            ProjectBatch::PHASE_PROCUREMENT => 'Batch Procurement telah dibuat. Konstruksi dapat memulai pekerjaan.',
            ProjectBatch::PHASE_KONSTRUKSI => 'Fase Konstruksi Batch telah selesai. Commerce dapat memproses Rekon.',
            ProjectBatch::PHASE_REKON => 'Fase Commerce/Rekon Batch telah aktif. Warehouse dapat memperbarui data.',
            ProjectBatch::PHASE_WAREHOUSE => 'Data Warehouse Batch telah disubmit. Finance dapat memproses invoice.',
            ProjectBatch::PHASE_FINANCE => 'Laporan Finance Batch telah disubmit. Menunggu pelunasan.',
            ProjectBatch::PHASE_CLOSED => 'Batch Project telah ditutup (Closed).',
            default => 'Silakan periksa detail update pada Batch ini.',
        };
    }
}

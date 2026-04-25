<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\User;
use App\Notifications\ProjectPhaseReminder;
use Illuminate\Support\Facades\Notification;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        $this->notifyRoleForPhase($project);
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        if ($project->isDirty('fase')) {
            $this->notifyRoleForPhase($project);
        }
    }

    /**
     * Notify the role responsible for the current project phase.
     */
    protected function notifyRoleForPhase(Project $project): void
    {
        // If the project is part of a batch, the ProjectBatchObserver will handle notifications
        // to avoid duplicate alerts for every site in the batch.
        if ($project->batch_id) {
            return;
        }

        $phaseRoleMap = [
            Project::PHASE_PLANNING => 'procurement',
            Project::PHASE_PROCUREMENT => 'konstruksi',
            Project::PHASE_KONSTRUKSI => 'commerce',
            Project::PHASE_REKON => 'warehouse',
            Project::PHASE_WAREHOUSE => 'finance',
            Project::PHASE_FINANCE => 'finance',
        ];

        $targetRole = $phaseRoleMap[$project->fase] ?? null;

        if ($targetRole) {
            $users = User::where('role', $targetRole)->get();
            
            if ($users->isNotEmpty()) {
                $message = $this->getNotificationMessage($project->fase);
                Notification::send($users, new ProjectPhaseReminder($project, $message));
            }
        }
    }

    /**
     * Get the notification message based on the phase.
     */
    protected function getNotificationMessage(string $phase): string
    {
        return match ($phase) {
            Project::PHASE_PLANNING => 'Project baru telah dibuat. Segera update detail fase Procurement.',
            Project::PHASE_PROCUREMENT => 'Fase Procurement telah selesai. Segera update detail fase Konstruksi.',
            Project::PHASE_KONSTRUKSI => 'Fase Konstruksi telah selesai. Segera update detail fase Commerce.',
            Project::PHASE_REKON => 'Fase Commerce/Rekon sedang aktif. Segera periksa data di Warehouse.',
            Project::PHASE_FINANCE => 'Fase Warehouse telah disubmit. Segera update detail fase Finance.',
            Project::PHASE_CLOSED => 'Project telah selesai (Closed).',
            default => 'Silakan periksa detail update pada project ini.',
        };
    }
}

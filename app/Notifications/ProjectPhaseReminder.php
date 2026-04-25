<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProjectPhaseReminder extends Notification
{
    use Queueable;

    protected $project;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($project, string $message)
    {
        $this->project = $project;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $roleMapping = [
            Project::PHASE_PLANNING => 'procurement',
            Project::PHASE_PROCUREMENT => 'konstruksi',
            Project::PHASE_KONSTRUKSI => 'commerce',
            Project::PHASE_REKON => 'warehouse',
            Project::PHASE_WAREHOUSE => 'finance',
            Project::PHASE_FINANCE => 'finance',
        ];

        $role = $roleMapping[$this->project->fase] ?? 'procurement';

        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->project_name,
            'fase' => $this->project->fase,
            'role' => $role,
            'message' => $this->message,
            'url' => route('tasks.manage', [$role, $this->project]),
        ];
    }
}

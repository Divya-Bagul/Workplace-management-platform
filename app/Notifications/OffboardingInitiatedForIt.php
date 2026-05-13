<?php

namespace App\Notifications;

use App\Models\OffboardingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OffboardingInitiatedForIt extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public OffboardingRequest $offboardingRequest)
    {
        $this->offboardingRequest->loadMissing('employee');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->offboardingRequest->employee;

        return (new MailMessage)
            ->subject('Offboarding: asset recovery')
            ->line('An employee offboarding has been initiated.')
            ->line('Employee: '.($employee?->name ?? 'Unknown').' ('.($employee?->employee_code ?? '—').')')
            ->line('Last working day: '.$this->offboardingRequest->last_working_day?->toDateString())
            ->action('Open workplace app', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $employee = $this->offboardingRequest->employee;

        return [
            'title' => 'Offboarding initiated',
            'body' => 'Recover assets for '.($employee?->name ?? 'Unknown').' (LWD '.$this->offboardingRequest->last_working_day?->toDateString().').',
            'offboarding_request_id' => $this->offboardingRequest->id,
            'status' => $this->offboardingRequest->status,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\OnboardingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public OnboardingRequest $onboardingRequest,
        public string $headline,
    ) {
        $this->onboardingRequest->loadMissing('employee');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->onboardingRequest->employee;

        return (new MailMessage)
            ->subject($this->headline)
            ->line($this->headline)
            ->line('Employee: '.($employee?->name ?? 'Unknown').' ('.($employee?->employee_code ?? '—').')')
            ->line('Status: '.$this->onboardingRequest->status)
            ->action('Open workplace app', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->headline,
            'body' => 'Status is now '.$this->onboardingRequest->status.'.',
            'onboarding_request_id' => $this->onboardingRequest->id,
            'status' => $this->onboardingRequest->status,
            'action_url' => route('onboarding.show', $this->onboardingRequest),
        ];
    }
}

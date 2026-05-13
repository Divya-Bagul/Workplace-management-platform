<?php

namespace App\Notifications;

use App\Models\OnboardingRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OnboardingForwardedToIt extends Notification
{
    use Queueable;

    public function __construct(public OnboardingRequest $onboardingRequest)
    {
        $this->onboardingRequest->loadMissing('employee');
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $employee = $this->onboardingRequest->employee;

        return [
            'title' => 'IT setup requested',
            'body' => 'Onboarding for '.($employee?->name ?? 'Unknown').' ('.($employee?->employee_code ?? '—').') needs provisioning.',
            'onboarding_request_id' => $this->onboardingRequest->id,
            'status' => $this->onboardingRequest->status,
            'action_url' => route('onboarding.show', $this->onboardingRequest),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employee = $this->onboardingRequest->employee;

        return (new MailMessage)
            ->subject('IT setup requested for new hire')
            ->line('A new onboarding request needs IT provisioning.')
            ->line('Employee: '.($employee?->name ?? 'Unknown').' ('.($employee?->employee_code ?? '—').')')
            ->line('Current status: '.$this->onboardingRequest->status)
            ->action('Open workplace app', url('/'));
    }
}

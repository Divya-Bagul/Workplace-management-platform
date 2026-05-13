<?php

namespace App\Events;

use App\Models\OnboardingRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnboardingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OnboardingRequest $onboardingRequest)
    {
        $this->onboardingRequest->loadMissing(['employee', 'desk']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('workplace');
    }

    public function broadcastAs(): string
    {
        return 'onboarding.status';
    }

    public function broadcastWith(): array
    {
        return [
            'onboarding_id' => $this->onboardingRequest->id,
            'employee' => $this->onboardingRequest->employee?->only(['id', 'name', 'employee_code']),
            'status' => $this->onboardingRequest->status,
            'desk_id' => $this->onboardingRequest->desk_id,
        ];
    }
}

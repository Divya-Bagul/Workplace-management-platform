<?php

namespace App\Events;

use App\Models\OffboardingRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OffboardingStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OffboardingRequest $offboardingRequest)
    {
        $this->offboardingRequest->loadMissing(['employee']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('workplace');
    }

    public function broadcastAs(): string
    {
        return 'offboarding.status';
    }

    public function broadcastWith(): array
    {
        return [
            'offboarding_id' => $this->offboardingRequest->id,
            'employee' => $this->offboardingRequest->employee?->only(['id', 'name', 'employee_code']),
            'status' => $this->offboardingRequest->status,
            'last_working_day' => $this->offboardingRequest->last_working_day?->toDateString(),
        ];
    }
}

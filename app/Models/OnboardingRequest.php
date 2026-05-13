<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class OnboardingRequest extends Model
{
    public const IT_QUEUE_STATUSES = ['it_pending', 'it_in_progress', 'it_complete'];

    protected $fillable = [
        'employee_id',
        'created_by',
        'status',
        'desk_id',
        'submitted_to_it_at',
        'it_setup_started_at',
        'it_setup_completed_at',
        'desk_assigned_at',
        'completed_at',
        'hr_notes',
        'it_notes',
    ];

    protected $casts = [
        'submitted_to_it_at' => 'datetime',
        'it_setup_started_at' => 'datetime',
        'it_setup_completed_at' => 'datetime',
        'desk_assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function desk(): BelongsTo
    {
        return $this->belongsTo(Desk::class);
    }

    public function isVisibleToIt(): bool
    {
        return in_array($this->status, self::IT_QUEUE_STATUSES, true);
    }

    public function allowsHrDeskSetup(): bool
    {
        return in_array($this->status, ['draft', 'desk_assigned'], true);
    }

    public function allowsHrCompletion(): bool
    {
        return $this->status === 'it_complete';
    }

    public function tracksAssetProvision(): bool
    {
        return in_array($this->status, self::IT_QUEUE_STATUSES, true)
            || $this->status === 'completed';
    }

    public function assetProvisionStatus(): string
    {
        if (! $this->tracksAssetProvision()) {
            return 'not_applicable';
        }

        return $this->employee?->hasActiveAssets() ? 'provided' : 'pending';
    }

    /**
     * @return Collection<int, AssetAssignment>
     */
    public function activeEmployeeAssetAssignments(): Collection
    {
        if (! $this->employee) {
            return collect();
        }

        if ($this->employee->relationLoaded('assetAssignments')) {
            return $this->employee->assetAssignments
                ->filter(fn (AssetAssignment $assignment) => $assignment->returned_at === null)
                ->values();
        }

        return $this->employee->activeAssetAssignments()->with('asset.assetType')->get();
    }

    /**
     * @return array{steps: array<int, array<string, string>>, next: ?array<string, string>}
     */
    public function workflowGuide(?User $user = null): array
    {
        $steps = [
            [
                'key' => 'desk',
                'title' => __('Assign desk'),
                'module' => __('Onboarding'),
                'state' => $this->workflowStepState('desk'),
            ],
            [
                'key' => 'forward',
                'title' => __('Forward to IT'),
                'module' => __('Onboarding'),
                'state' => $this->workflowStepState('forward'),
            ],
            [
                'key' => 'it_setup',
                'title' => __('IT system setup'),
                'module' => __('Onboarding'),
                'state' => $this->workflowStepState('it_setup'),
            ],
            [
                'key' => 'assets',
                'title' => __('Provide IT assets'),
                'module' => __('IT assets'),
                'state' => $this->workflowStepState('assets'),
            ],
            [
                'key' => 'complete',
                'title' => __('Complete onboarding'),
                'module' => __('Onboarding'),
                'state' => $this->workflowStepState('complete'),
            ],
        ];

        return [
            'steps' => $steps,
            'next' => $user ? $this->nextWorkflowStepFor($user) : null,
        ];
    }

    private function workflowStepState(string $key): string
    {
        if ($this->status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->status === 'completed') {
            return 'complete';
        }

        return match ($key) {
            'desk' => $this->desk_id ? 'complete' : ($this->status === 'draft' ? 'current' : 'upcoming'),
            'forward' => in_array($this->status, ['it_pending', 'it_in_progress', 'it_complete'], true) || $this->submitted_to_it_at
                ? 'complete'
                : ($this->desk_id ? 'current' : 'upcoming'),
            'it_setup' => $this->status === 'it_complete' || $this->status === 'completed'
                ? 'complete'
                : (in_array($this->status, ['it_pending', 'it_in_progress'], true) ? 'current' : 'upcoming'),
            'assets' => ! $this->tracksAssetProvision()
                ? 'upcoming'
                : ($this->assetProvisionStatus() === 'provided' ? 'complete' : (in_array($this->status, ['it_pending', 'it_in_progress'], true) ? 'current' : 'upcoming')),
            'complete' => $this->status === 'completed'
                ? 'complete'
                : ($this->status === 'it_complete' ? 'current' : 'upcoming'),
            default => 'upcoming',
        };
    }

    /**
     * @return ?array<string, string>
     */
    private function nextWorkflowStepFor(User $user): ?array
    {
        if (in_array($this->status, ['completed', 'cancelled'], true)) {
            return null;
        }

        if ($user->hasAnyRole(['admin', 'hr']) && $this->allowsHrDeskSetup()) {
            if (! $this->desk_id) {
                return [
                    'role' => 'HR',
                    'label' => __('Assign a desk on this onboarding request.'),
                    'module' => __('Onboarding'),
                    'route' => route('onboarding.show', $this),
                ];
            }

            return [
                'role' => 'HR',
                'label' => __('Forward the request to IT for system setup.'),
                'module' => __('Onboarding'),
                'route' => route('onboarding.show', $this),
            ];
        }

        if ($user->hasRole('it') && in_array($this->status, ['it_pending', 'it_in_progress'], true)) {
            if ($this->assetProvisionStatus() === 'pending') {
                return [
                    'role' => 'IT',
                    'label' => __('Assign IT assets to the employee, then finish system setup.'),
                    'module' => __('IT assets'),
                    'route' => route('assets.index'),
                ];
            }

            return [
                'role' => 'IT',
                'label' => $this->status === 'it_pending'
                    ? __('Start setup, then mark system setup complete.')
                    : __('Mark system setup complete.'),
                'module' => __('Onboarding'),
                'route' => route('onboarding.show', $this),
            ];
        }

        if ($user->hasAnyRole(['admin', 'hr']) && $this->allowsHrCompletion()) {
            return [
                'role' => 'HR',
                'label' => __('Mark onboarding completed after IT setup is done.'),
                'module' => __('Onboarding'),
                'route' => route('onboarding.show', $this),
            ];
        }

        if ($user->hasRole('it') && $this->status === 'it_complete') {
            return [
                'role' => 'IT',
                'label' => __('Waiting for HR to close this onboarding request.'),
                'module' => __('Onboarding'),
            ];
        }

        return null;
    }
}

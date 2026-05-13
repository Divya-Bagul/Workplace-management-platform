<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->foreignId('desk_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('submitted_to_it_at')->nullable();
            $table->timestamp('it_setup_started_at')->nullable();
            $table->timestamp('it_setup_completed_at')->nullable();
            $table->timestamp('desk_assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('hr_notes')->nullable();
            $table->text('it_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_requests');
    }
};

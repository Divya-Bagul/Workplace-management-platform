<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offboarding_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->date('last_working_day');
            $table->string('status')->default('pending');
            $table->timestamp('assets_recovery_started_at')->nullable();
            $table->timestamp('assets_recovered_at')->nullable();
            $table->timestamp('desk_released_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('hr_notes')->nullable();
            $table->text('it_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offboarding_requests');
    }
};

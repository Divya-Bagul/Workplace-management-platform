<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('designation')->nullable();
            $table->date('joining_date')->nullable();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('floor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employment_status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

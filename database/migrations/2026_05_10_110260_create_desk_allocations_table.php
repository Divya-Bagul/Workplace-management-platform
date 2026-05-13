<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desk_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('desk_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['desk_id', 'valid_to']);
            $table->index(['employee_id', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desk_allocations');
    }
};

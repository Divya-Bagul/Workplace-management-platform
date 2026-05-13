<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};

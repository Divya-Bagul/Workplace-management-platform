<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('floor_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('status')->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['floor_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desks');
    }
};

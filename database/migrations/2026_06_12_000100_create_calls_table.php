<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('callee_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 10);   // audio | video
            // ringing | ongoing | ended | missed | declined | canceled
            $table->string('status', 12)->default('ringing');
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['caller_id', 'created_at']);
            $table->index(['callee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};

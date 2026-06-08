<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Polymorphic: a reaction belongs to a post OR a comment.
            $table->morphs('reactable');
            $table->string('type', 16); // like | love | haha | wow | sad | angry | care
            $table->timestamps();

            // One reaction per user per target (its type can change).
            $table->unique(['user_id', 'reactable_id', 'reactable_type'], 'reactions_user_target_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};

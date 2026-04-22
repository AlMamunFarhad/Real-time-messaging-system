<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_call_signals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('from_id');
            $table->string('from_type');
            $table->unsignedBigInteger('to_id');
            $table->string('to_type');
            $table->enum('type', ['offer', 'answer', 'ice_candidate', 'hangup', 'reject', 'ringing']);
            $table->longText('payload')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();

            $table->index(['conversation_id', 'to_id', 'to_type', 'is_processed', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_call_signals');
    }
};

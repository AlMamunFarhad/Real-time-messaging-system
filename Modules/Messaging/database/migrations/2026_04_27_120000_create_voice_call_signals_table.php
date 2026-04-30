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
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('from_id');
            $table->string('from_type');
            $table->unsignedBigInteger('to_id');
            $table->string('to_type');
            $table->string('type');
            $table->string('call_mode')->nullable();
            $table->longText('payload')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamps();

            $table->index(['conversation_id', 'to_id', 'to_type', 'is_processed'], 'voice_call_signal_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_call_signals');
    }
};

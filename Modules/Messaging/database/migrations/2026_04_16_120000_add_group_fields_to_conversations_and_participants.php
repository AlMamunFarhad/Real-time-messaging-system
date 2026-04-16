<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('name')->nullable()->after('type');
            $table->text('description')->nullable()->after('name');
            $table->boolean('is_group')->default(false)->after('description');
            $table->unsignedBigInteger('created_by_id')->nullable()->after('is_group');
            $table->string('created_by_type')->nullable()->after('created_by_id');
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->string('role')->default('member')->after('participant_type');
            $table->unsignedBigInteger('added_by_id')->nullable()->after('role');
            $table->string('added_by_type')->nullable()->after('added_by_id');
            $table->timestamp('left_at')->nullable()->after('joined_at');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_participants', function (Blueprint $table) {
            $table->dropColumn(['role', 'added_by_id', 'added_by_type', 'left_at']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['name', 'description', 'is_group', 'created_by_id', 'created_by_type']);
        });
    }
};

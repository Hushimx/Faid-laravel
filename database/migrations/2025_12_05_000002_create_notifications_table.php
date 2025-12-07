<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->json('title'); // Multilingual: {en: '', ar: ''}
            $table->json('body'); // Multilingual: {en: '', ar: ''}
            $table->enum('target_type', ['all', 'role', 'individual'])->default('all');
            $table->json('target_value')->nullable(); // For role or user IDs
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('data')->nullable(); // Additional custom payload
            $table->timestamps();

            $table->index(['admin_id', 'created_at']);
            $table->index('target_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

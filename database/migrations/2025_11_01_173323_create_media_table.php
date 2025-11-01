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
    Schema::create('media', function (Blueprint $table) {
      $table->id();
      $table->morphs('mediable'); // mediable_type, mediable_id (services/products) - automatically creates index
      $table->string('type'); // image, video
      $table->string('path'); // storage path
      $table->string('mime_type')->nullable();
      $table->integer('size')->nullable(); // in bytes
      $table->integer('order')->default(0); // for ordering
      $table->boolean('is_primary')->default(false); // primary image/video
      $table->json('meta')->nullable(); // width, height, duration, etc.
      $table->timestamps();

      // Additional indexes
      $table->index(['type', 'is_primary']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('media');
  }
};

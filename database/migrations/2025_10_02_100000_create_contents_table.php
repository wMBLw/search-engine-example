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
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->string('external_id')->index();
            $table->enum('type', ['video', 'article']);
            $table->string('title')->nullable();
            $table->integer('views')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('comments')->default(0);
            $table->integer('reactions')->default(0);
            $table->integer('reading_time')->default(0);
            $table->integer('during_seconds')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'external_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};

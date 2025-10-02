<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name',30)->unique();
            $table->enum('type',['xml','json']);
            $table->string('endpoint');
            $table->json('config')->nullable(); // headers, auth, paging etc.
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_synced_at')->nullable()->index();
            $table->integer('consecutive_failures')->default(0); //required for circuit-breaker pattern
            $table->timestamp('disabled_until')->nullable()->index(); //required for circuit-breaker pattern
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};

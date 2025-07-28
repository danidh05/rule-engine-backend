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
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->integer('salience')->default(0); // Priority, lower fires first
            $table->boolean('stackable')->default(true);
            $table->jsonb('condition_json'); // Structured JSON condition
            $table->jsonb('action_json'); // Structured JSON action
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('salience');
            $table->index('is_active');
            $table->index('stackable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rules');
    }
};
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
        Schema::create('neural_filters', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('prompt')->nullable();
            $table->text('simpleFilter')->nullable();
            $table->boolean('activePrompt')->default(false);
            $table->boolean('activeSimple')->default(false);
            $table->foreignId('neural_id')->constrained('neurals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neural_filters');
    }
};

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
        Schema::create('neurals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40);
            $table->string('show_name', 40);
            $table->unsignedTinyInteger('temperature')->default(50);
            $table->string('description', 150)->nullable();
            $table->unsignedTinyInteger('countLastMessage')->default(5);
            $table->foreignId('base_prompt_id')->nullable()->constrained('base_prompts')->onDelete('set null');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('neurals');
    }
};

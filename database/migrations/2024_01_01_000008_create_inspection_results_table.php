<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('inspection_item_id')->constrained('inspection_items')->restrictOnDelete();
            $table->enum('result', ['pass', 'fail', 'na'])->nullable();
            $table->text('remark')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->unique(['inspection_id', 'inspection_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_results');
    }
};

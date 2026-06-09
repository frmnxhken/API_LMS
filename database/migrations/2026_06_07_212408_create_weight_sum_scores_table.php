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
        Schema::create('weight_sum_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_subject_id')->constrained()->cascadeOnDelete();
            $table->decimal('assignment_weight', 3, 2)->default(0.20);
            $table->decimal('daily_weight', 3, 2)->default(0.20);
            $table->decimal('uts_weight', 3, 2)->default(0.30);
            $table->decimal('uas_weight', 3, 2)->default(0.30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_sum_scores');
    }
};

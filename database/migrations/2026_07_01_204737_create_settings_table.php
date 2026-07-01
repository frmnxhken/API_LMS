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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7)->default(-7.1167915);
            $table->decimal('longitude', 10, 7)->default(112.4068287);
            $table->integer('radius')->default(50);
            $table->time('open_time')->default('06:00:00');
            $table->time('start_time')->default('07:00:00');
            $table->time('late_time')->default('08:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

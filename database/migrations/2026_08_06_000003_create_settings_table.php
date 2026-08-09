<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('theme')->default('light');
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('drink_water_reminder')->default(true);
            $table->boolean('period_reminder')->default(true);
            $table->boolean('cycle_reminder')->default(true);
            $table->string('water_interval_minutes', 5)->default('60');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

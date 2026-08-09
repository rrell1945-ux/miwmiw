<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('password');
        });

        Schema::table('periods', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('notes');
        });

        Schema::create('period_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->date('day_date');
            $table->string('flow')->nullable();
            $table->string('mood')->nullable();
            $table->json('symptoms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['period_id', 'day_date']);
            $table->index(['period_id', 'day_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_days');

        Schema::table('periods', function (Blueprint $table) {
            $table->dropColumn('active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

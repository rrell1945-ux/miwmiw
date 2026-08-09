<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['period_day_id']);
            $table->dropUnique('messages_period_day_id_unique');
            $table->foreign('period_day_id')
                ->references('id')
                ->on('period_days')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['period_day_id']);
            $table->unique('period_day_id');
            $table->foreign('period_day_id')
                ->references('id')
                ->on('period_days')
                ->cascadeOnDelete();
        });
    }
};

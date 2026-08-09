<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periods', function (Blueprint $table) {
            $table->string('status', 20)->default('completed')->after('end_date');
        });

        DB::table('periods')->where('active', true)->update(['status' => 'ongoing']);

        Schema::table('periods', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->index(['user_id', 'status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::table('periods', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status', 'start_date']);
            $table->boolean('active')->default(true)->after('notes');
        });

        DB::table('periods')->where('status', 'ongoing')->update(['active' => true]);

        Schema::table('periods', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

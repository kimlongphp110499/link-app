<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Xóa cột "points_added" nếu tồn tại
        if (DB::getSchemaBuilder()->hasColumn('clan_point_histories', 'points_added')) {
            Schema::table('clan_point_histories', function (Blueprint $table) {
                $table->dropColumn('points_added');
            });
        }

        // Xóa cột "points" nếu tồn tại
        if (DB::getSchemaBuilder()->hasColumn('clans', 'points')) {
            Schema::table('clans', function (Blueprint $table) {
                $table->dropColumn('points');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clan_point_histories', function (Blueprint $table) {
            $table->integer('points_added')->nullable();
        });

        Schema::table('clans', function (Blueprint $table) {
            $table->integer('points')->nullable();
        });
    }
};

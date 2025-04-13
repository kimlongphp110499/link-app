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
        Schema::table('clan_point_histories', function (Blueprint $table) {
            if (Schema::hasColumn('clan_point_histories', 'point_added')) {
                $table->dropColumn('point_added');
            }
        });

        Schema::table('clans', function (Blueprint $table) {
            if (Schema::hasColumn('clans', 'points')) {
                $table->dropColumn('points');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clan_point_histories', function (Blueprint $table) {
            $table->integer('point_added')->nullable();
        });

        Schema::table('clans', function (Blueprint $table) {
            $table->integer('points')->nullable();
        });
    }
};

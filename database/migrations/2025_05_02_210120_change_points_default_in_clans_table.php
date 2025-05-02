<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('clans')
            ->whereNull('points')
            ->update(['points' => 0]);
        Schema::table('clans', function (Blueprint $table) {
            $table->integer('points')->default(0)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clans', function (Blueprint $table) {
            $table->integer('points')->default(null)->nullable()->change();
        });
    }
};

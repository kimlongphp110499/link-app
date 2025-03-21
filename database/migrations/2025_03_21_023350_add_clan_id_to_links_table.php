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
        Schema::table('links', function (Blueprint $table) {
            $table->unsignedBigInteger('clan_id')->nullable()->unique(); // Thêm cột clan_id, nullable vì có thể không có clan.
            $table->foreign('clan_id')->references('id')->on('clans')->onDelete('set null'); // Thêm khóa ngoại
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['clan_id']); 
            $table->dropColumn('clan_id');
        });
    }
};

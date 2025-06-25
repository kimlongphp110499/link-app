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
        Schema::table('clan_temp_members', function (Blueprint $table) {
            // Sửa kiểu dữ liệu các cột
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('link_id')->change();
            $table->unsignedBigInteger('clan_id')->change();

            // Thêm ràng buộc khóa ngoại
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('link_id')
                  ->references('id')
                  ->on('links')
                  ->onDelete('cascade');

            $table->foreign('clan_id')
                  ->references('id')
                  ->on('clans')
                  ->onDelete('cascade');

            // Thêm cột deleted_at nếu chưa có
            if (!Schema::hasColumn('clan_temp_members', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clan_temp_members', function (Blueprint $table) {
            // Xóa khóa ngoại
            $table->dropForeign(['user_id']);
            $table->dropForeign(['link_id']);
            $table->dropForeign(['clan_id']);

            // Xóa cột deleted_at nếu tồn tại
            if (Schema::hasColumn('clan_temp_members', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            // Khôi phục kiểu string (nếu cần)
            $table->string('user_id')->nullable(false)->change();
            $table->string('link_id')->nullable(false)->change();
            $table->string('clan_id')->nullable(false)->change();
        });
    }
};
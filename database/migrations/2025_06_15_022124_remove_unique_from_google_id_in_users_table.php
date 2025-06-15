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
       Schema::table('users', function (Blueprint $table) {
            $indexes = DB::select('SHOW INDEXES FROM users WHERE Column_name = ?', ['google_id']);

            foreach ($indexes as $index) {
                if ($index->Non_unique == 0 && strpos($index->Key_name, 'google_id') !== false) {
                    $table->dropUnique($index->Key_name);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('google_id', 'users_google_id_unique');
        });
    }
};

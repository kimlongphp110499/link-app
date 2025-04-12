<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToOptimizeQueries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add indexes to the links table
        Schema::table('links', function (Blueprint $table) {
            $table->index('total_votes', 'index_total_votes'); // Index for WHERE and ORDER BY
            $table->index('is_played', 'index_is_played');     // Index for WHERE
            $table->index('id', 'index_id');                  // Index for ORDER BY and JOIN
        });

        // Add indexes to the vote_histories table
        Schema::table('vote_histories', function (Blueprint $table) {
            $table->index('link_id', 'index_vote_histories_link_id'); // Index for WHERE and JOIN
        });

        // Add indexes to the schedules table
        Schema::table('schedules', function (Blueprint $table) {
            $table->index('link_id', 'index_schedules_link_id'); // Index for WHERE and JOIN
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop indexes from the links table
        Schema::table('links', function (Blueprint $table) {
            $table->dropIndex('index_total_votes');
            $table->dropIndex('index_is_played');
            $table->dropIndex('index_id');
        });

        // Drop indexes from the vote_histories table
        Schema::table('vote_histories', function (Blueprint $table) {
            $table->dropIndex('index_vote_histories_link_id');
        });

        // Drop indexes from the schedules table
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex('index_schedules_link_id');
        });
    }
}
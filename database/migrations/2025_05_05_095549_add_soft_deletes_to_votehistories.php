<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToVotehistories extends Migration
{
    public function up()
    {
        Schema::table('vote_histories', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('vote_histories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
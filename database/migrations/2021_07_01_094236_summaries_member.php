<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SummariesMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('summaries_member', function (Blueprint $table) {
            $table->bigInteger('member_id')->primary();
            $table->integer('count_approved_depo');
            $table->decimal('sum_approved_depo', 16, 2);
            $table->integer('count_rejected_depo');
            $table->decimal('sum_rejected_depo', 16, 2);
            $table->integer('count_approved_wd');
            $table->decimal('sum_approved_wd', 16, 2);
            $table->integer('count_rejected_wd');
            $table->decimal('sum_rejected_wd', 16, 2);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->dateTime('last_deposit');
            $table->dateTime('last_withdraw');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

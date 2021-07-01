<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DepositMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits_migrations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->bigInteger('transaction_id');
            $table->integer('branch_code');
            $table->string('branch_name');
            $table->string('currency_code');
            $table->integer('member_id');
            $table->string('play_id');
            $table->string('username');
            $table->string('from_payment_type_name');
            $table->string('from_payment_name');
            $table->string('from_account_name');
            $table->string('from_account_number');
            $table->string('to_payment_type_name');
            $table->string('to_payment_name');
            $table->string('to_account_name');
            $table->string('to_account_number');
            $table->float('rate');
            $table->float('amount_submitted');
            $table->float('amount_processed');
            $table->string('notes');
            $table->string('proof');
            $table->string('remark');
            $table->tinyInteger('status');
            $table->tinyInteger('warning');
            $table->dateTime('created_at');
            $table->string('processed_by');
            $table->dateTime('processed_at');
            $table->string('updated_by');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposits_migrations');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WlpaymentMigrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wl_payments_migrations', function (Blueprint $table) {
            $table->id();
            $table->integer('bank_id');
            $table->integer('branch_code');
            $table->tinyInteger('status');
            $table->tinyInteger('register');
            $table->tinyInteger('deposit');
            $table->tinyInteger('order');
            $table->string('description');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->string('updated_by')->nullable();
            $table->dateTime('deleted_at');
            $table->string('deleted_by');
            $table->uuid('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wl_payments_migrations');
    }
}

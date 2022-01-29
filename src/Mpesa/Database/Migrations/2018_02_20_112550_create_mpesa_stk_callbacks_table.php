<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaStkCallbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_stk_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_request_id')->index();
            $table->string('checkout_request_id')->index();
            $table->integer('result_code');
            $table->string('result_desc');
            $table->decimal('amount')->nullable();
            $table->string('mpesa_receipt_number')->nullable();
            $table->decimal('balance')->nullable();
            $table->string('phone')->nullable();
            $table->string('transaction_date')->nullable();

            $table->timestamps();
            $table->foreign('checkout_request_id')
                ->references('checkout_request_id')
                ->on('mpesa_stk_requests')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_stk_callbacks');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaBulkPaymentResponsesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_bulk_payment_responses', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('result_type');
            $table->smallInteger('result_code');
            $table->string('result_desc');
            $table->string('originator_conversation_id');
            $table->string('conversation_id');
            $table->string('transaction_id');
            $table->timestamps();

            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('mpesa_bulk_payment_requests')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_bulk_payment_responses');
    }
}

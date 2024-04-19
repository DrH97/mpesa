<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaTransactionStatusRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_transaction_status_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('conversation_id')->index();
            $table->string('originator_conversation_id');
            $table->string('response_code', 5);
            $table->string('response_description');

            $table->string('relation_type', 32)->nullable();
            $table->unsignedInteger('relation_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_transaction_status_requests');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_transaction_status_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('result_type');
            $table->string('result_code');
            $table->string('result_desc');
            $table->string('originator_conversation_id');
            $table->string('conversation_id');
            $table->string('transaction_id');

            // result params
            $table->string('result_originator_conversation_id')->nullable();
            $table->string('result_conversation_id')->nullable();

            $table->string('debit_party_name')->nullable();
            $table->string('credit_party_name')->nullable();
            $table->string('initiated_time')->nullable();
            $table->string('debit_account_type')->nullable();
            $table->string('debit_party_charges')->nullable();
            $table->string('transaction_reason')->nullable();
            $table->string('reason_type')->nullable();
            $table->string('transaction_status')->nullable();
            $table->string('finalised_time')->nullable();
            $table->decimal('amount')->nullable();
            $table->string('receipt_no')->nullable();

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
        Schema::dropIfExists('mpesa_transaction_status_callbacks');
    }
};

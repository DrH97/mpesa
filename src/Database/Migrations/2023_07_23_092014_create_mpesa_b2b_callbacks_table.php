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
        Schema::create('mpesa_b2b_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->smallInteger('result_type');
            $table->string('result_code');
            $table->string('result_desc');
            $table->string('originator_conversation_id');
            $table->string('conversation_id');
            $table->string('transaction_id');

            // result params
            $table->string('debit_account_balance')->nullable();
            $table->decimal('amount')->nullable();
            $table->string('debit_party_affected_account_balance')->nullable();
            $table->string('trans_completed_time')->nullable();
            $table->string('debit_party_charges')->nullable();
            $table->string('receiver_party_public_name')->nullable();
            $table->string('currency')->nullable();
            $table->string('initiator_account_current_balance')->nullable();

            $table->string('debit_account_current_balance')->nullable();
            $table->string('credit_account_balance')->nullable();
            $table->string('debit_party_public_name')->nullable();
            $table->string('credit_party_public_name')->nullable();

            $table->foreign('conversation_id')
                ->references('conversation_id')
                ->on('mpesa_b2b_requests')->onDelete('restrict')->onUpdate('restrict');
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
        Schema::dropIfExists('mpesa_b2b_callbacks');
    }
};

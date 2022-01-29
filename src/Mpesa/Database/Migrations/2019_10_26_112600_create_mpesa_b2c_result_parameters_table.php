<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaB2cResultParametersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_b2c_result_parameters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('response_id');
            $table->decimal('transaction_amount');
            $table->string('transaction_receipt')->unique();
            $table->char('b2c_recipient_is_registered_customer', 1);
            $table->decimal('b2c_charges_paid_account_available_funds');
            $table->string('receiver_party_public_name');
            $table->decimal('b2c_utility_account_available_funds', 11);
            $table->decimal('b2c_working_account_available_funds', 11);
            $table->timestamp('transaction_completed_date_time');
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
        Schema::dropIfExists('mpesa_b2c_result_parameters');
    }
}

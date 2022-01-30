<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaC2bCallbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'mpesa_c2b_callbacks',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('transaction_type');
                $table->string('trans_id')->unique();
                $table->string('trans_time');
                $table->decimal('trans_amount');
                $table->integer('business_short_code');
                $table->string('bill_ref_number')->nullable();
                $table->string('invoice_number')->nullable();
                $table->string('third_party_trans_id')->nullable();
                $table->decimal('org_account_balance', 11);
                $table->string('msisdn');
                $table->string('first_name')->nullable();
                $table->string('middle_name')->nullable();
                $table->string('last_name')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mpesa_c2b_callbacks');
    }
}

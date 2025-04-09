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
        Schema::create('mpesa_b2b_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('command_id')->default('BusinessPayBill');
            $table->string('party_a', 10);
            $table->string('party_b', 10);
            $table->string('requester', 20)->nullable();
            $table->decimal('amount', 10);
            $table->string('account_reference');
            $table->string('remarks')->nullable();

            $table->string('conversation_id')->index();
            $table->string('originator_conversation_id');
            $table->string('response_code', 5);
            $table->string('response_description');

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
        Schema::dropIfExists('mpesa_b2b_requests');
    }
};

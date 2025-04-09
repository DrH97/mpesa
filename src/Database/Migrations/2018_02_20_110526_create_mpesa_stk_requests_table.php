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
        Schema::create(
            'mpesa_stk_requests',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('phone', 15);
                $table->decimal('amount');
                $table->string('reference');
                $table->string('description');
                $table->string('status')->default('REQUESTED');
                $table->string('merchant_request_id')->unique();
                $table->string('checkout_request_id')->unique();
                $table->unsignedInteger('relation_id')->nullable();
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
        Schema::dropIfExists('mpesa_stk_requests');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained();
            $table->string('reference')->unique('reference')->nullable();
            $table->enum('mode', ['buy', 'sell'])->index();
            $table->enum('coupon_type', ['ecode', 'physical'])->nullable();
            $table->unsignedBigInteger('coupon_id');
            $table->string('coupon_currency_type_id');
            $table->string('ecode')->nullable();
            $table->string('coupon_front')->nullable();
            $table->string('coupon_back')->nullable();
            $table->decimal('units', 19, 4);
            $table->decimal('rate', 19, 4)->default(0);
            $table->decimal('total_payable', 19, 4);
            $table->decimal('fee', 19, 4)->default(0);
            $table->text('remark')->nullable();
            $table->enum('status', ['pending', 'success', 'failed', 'cancelled']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

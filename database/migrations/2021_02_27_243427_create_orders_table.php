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
            $table->string('reference')->unique('reference')->nullable();
            $table->foreignId('user_id')->index()->constrained();
            $table->enum('mode', ['buy', 'sell'])->index();
            $table->enum('order_type', ['crypto', 'coupon']);
            $table->unsignedBigInteger('order_id');
            $table->string('currency')->nullable();
            $table->string('coupon_type')->nullable();
            $table->decimal('volume', 19, 4);
            $table->decimal('rate', 19, 4)->default(0);
            $table->decimal('unit_price', 19, 4)->default(0);
            $table->decimal('total_payable', 19, 4);
            $table->decimal('fee', 19, 4)->default(0);
            $table->text('remark')->nullable();
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['order_type', 'order_id']);
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

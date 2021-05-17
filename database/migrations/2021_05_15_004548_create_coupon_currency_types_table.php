<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponCurrencyTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupon_currency_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->index()->constrained();
            $table->string('name');
            $table->decimal('buy_rate', 19,4)->default(1);
            $table->decimal('sell_rate', 19,4)->default(1);
            $table->decimal('buy_margin', 19,4)->default(0);
            $table->decimal('sell_margin', 19,4)->default(0);
            $table->tinyInteger('precision')->length(1)->default(2); // decimal place to round to during conversion
            $table->boolean('is_available')->default(true);
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
        Schema::dropIfExists('coupon_currency_types');
    }
}

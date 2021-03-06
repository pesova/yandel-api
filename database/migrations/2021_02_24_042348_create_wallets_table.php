<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('code')->nullable();
            $table->string('currency')->nullable();
            $table->text('image_url')->nullable();
            $table->decimal('buy_rate', 19,4)->default(1);
            $table->decimal('sell_rate', 19,4)->default(1);
            $table->decimal('buy_margin', 19,4)->default(0);
            $table->decimal('sell_margin', 19,4)->default(0);
            $table->decimal('deposit_fee', 19,4)->default(0);
            $table->decimal('withdrawal_fee', 19,4)->default(0);
            $table->boolean('is_available')->default(true);
            $table->boolean('is_visible')->default(true);

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
        Schema::dropIfExists('wallets');
    }
}

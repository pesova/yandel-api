<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->index();
            $table->string('asset_code')->constrained();
            $table->text('image_url')->constrained();
            $table->json('acceptable_currencies')->nullable();
            $table->json('acceptable_types')->nullable();
            $table->decimal('buy_rate', 19,4)->default(0);
            $table->decimal('sell_rate', 19,4)->default(0);
            $table->decimal('buy_margin', 19,4)->default(0);
            $table->decimal('sell_margin', 19,4)->default(0);
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
        Schema::dropIfExists('gift_cards');
    }
}

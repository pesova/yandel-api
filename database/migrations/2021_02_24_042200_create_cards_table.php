<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index()->constrained();        
            $table->enum('gateway', ['paystack']);
            $table->string('bank');
            $table->string('type', 20);
            $table->char('last4', 4);
            $table->char('expiration_year', 4) ;        
            $table->char('expiration_month', 2);
            $table->string('token')->unique('token');
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
        Schema::dropIfExists('cards');
    }
}

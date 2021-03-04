<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 4)->index();
            $table->string('symbol')->nullable();
            $table->string('unicode')->index()->nullable();
            $table->tinyInteger('precision')->length(1)->default(2); // decimal place to round to during conversion
            $table->decimal('last_naira_exchange_rate', 19,4)->default(0); // may not be necessary if rate is pulled on the fly
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
        Schema::dropIfExists('currencies');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->string('channel');
            $table->string('subscriber', 30);
            $table->string('event', 30);
            $table->string('token', 60);
            $table->boolean('verified')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['channel', 'subscriber', 'event']);
            $table->unique(['channel', 'subscriber', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tokens');
    }
}

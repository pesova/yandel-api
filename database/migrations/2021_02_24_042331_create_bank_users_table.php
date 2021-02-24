<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index('user_id')->constrained();
            $table->foreignId('bank_id')->constrained();
            $table->string('nuban')->index();
            $table->string('name', 100);
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
        Schema::dropIfExists('bank_users');
    }
}

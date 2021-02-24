<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('wallet_id')->constrained();
                $table->unique(['user_id', 'wallet_id']); // a user cant own 2 instances of same wallet
                $table->index(['user_id', 'wallet_id']);

            $table->decimal('book_balance', 19,4)->default(0);
            $table->decimal('available_balance', 19,4)->default(0);

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
        Schema::dropIfExists('wallet_users');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique('reference')->nullable();
            $table->foreignId('user_id')->index()->constrained();

            $table->enum('type', ['deposit', 'withdrawal']);
            $table->enum('source_type', ['wallet', 'crypto', 'card'])->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->enum('destination_type', ['wallet', 'bank', 'crypto'])->nullable();
            $table->unsignedBigInteger('destination_id')->nullable();

            $table->decimal('amount', 19,4)->default(0)->comment('Amount of money that was credited or debited');
            $table->decimal('fees', 19,4)->default(0)->nullable();
            $table->decimal('balance', 19,4)->default(0)->comment('balance after debit / credit has been recorded');         
            $table->string('remark');
            
            $table->enum('status', ['success', 'failed', 'pending', 'abandoned']);
            $table->text('gateway_response')->nullable();
            $table->string('retry_count')->nullable();

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
        Schema::dropIfExists('transactions');
    }
}

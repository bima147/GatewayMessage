<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
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
            $table->string('phone');
            $table->text('message');
            $table->string('image')->nullable();
            $table->string('caption')->nullable();
            $table->integer('price');
            $table->date('send_date')->nullable();
            $table->time('send_time', $precision = 0)->nullable();
            $table->enum('status', ['Waiting', 'Sent', 'Delivered', 'Error', 'Readed', 'Failed']);
            $table->unsignedBigInteger('users_id');
            $table->foreign('users_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('type', ['wareguler', 'reguler']);
            $table->string('message_id')->nullable();
            $table->timestamps();
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
};

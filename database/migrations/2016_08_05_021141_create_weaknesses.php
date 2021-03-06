<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWeaknesses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weaknesses',function(Blueprint $table){
            $table->increments('id_weakness');
            $table->string('value');
            $table->integer('id_card')->unsigned();
            $table->timestamps();
        });

        Schema::table('weaknesses',function(Blueprint $table){
            $table->foreign('id_card')->references('id_card')->on('cards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('weaknesses');
    }
}

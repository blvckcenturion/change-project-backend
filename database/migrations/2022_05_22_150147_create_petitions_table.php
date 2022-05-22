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
        Schema::create('petitions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->bigInteger('userId')->unsigned()->index();
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade')->onUpdate('no action');
            $table->timestamps();
            $table->string('title');
            $table->string('directedTo');
            $table->string('description');
            $table->integer('goal');
            $table->boolean('isGoalCompleted');
            $table->string('imageUrl');
            //1
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('petitions');
    }
};

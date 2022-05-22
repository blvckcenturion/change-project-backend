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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('petitionId')->unsigned()->index();
            $table->foreign('petitionId')->references('id')->on('petitions')->onDelete('cascade')->onUpdate('no action');
            $table->bigInteger('userId')->unsigned()->index();
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade')->onUpdate('no action');
            $table->string('comment');
            $table->integer('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comments');
    }
};

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
            $table->boolean('isGoalCompleted')->default(false);
            $table->string('imageUrl')->nullable();
            $table->integer('signatureCount')->default(0);
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists('petitions');
    }
};

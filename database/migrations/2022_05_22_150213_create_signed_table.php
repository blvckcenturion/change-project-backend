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
        Schema::create('signeds', function (Blueprint $table) {
            $table->bigInteger('petitionId')->unsigned()->index();
            $table->bigInteger('userId')->unsigned()->index();
            $table->timestamps();
            $table->primary(['petitionId', 'userId']);
            $table->foreign('petitionId')->references('id')->on('petitions')->onDelete('cascade')->onUpdate('no action');
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade')->onUpdate('no action');
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
        Schema::dropIfExists('signed');
    }
};

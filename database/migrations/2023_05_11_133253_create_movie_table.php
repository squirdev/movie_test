<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid');
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->unsignedBigInteger('cast_id');
            $table->string('title',100);
            $table->string('release_year',10);
            $table->string('tag',20);
            $table->string('poster',255);
            $table->string('rent_price',10)->nullable();
            $table->string('imdbID',10)->nullable();
            $table->string('stramingId',10)->nullable();
            $table->string('stream_url',255)->nullable();
            $table->string('status',10);
            $table->dateTime('rent_start')->nullable();
            $table->dateTime('rent_end')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade');
            $table->foreign('cast_id')->references('id')->on('casts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
}

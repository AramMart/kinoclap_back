<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news_resource', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('news_id');
            $table->foreign('news_id')
                ->references('id')
                ->on('news')->onDelete('cascade');

            $table->unsignedBigInteger('resource_id');
            $table->foreign('resource_id')
                ->references('id')
                ->on('resources')->onDelete('cascade');
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
        Schema::dropIfExists('news_resource');
    }
}

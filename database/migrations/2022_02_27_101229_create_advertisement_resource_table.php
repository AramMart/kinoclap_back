<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvertisementResourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertisement_resource', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('advertisement_id');
            $table->foreign('advertisement_id')
                ->references('id')
                ->on('advertisements')->onDelete('cascade');

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
        Schema::dropIfExists('advertisement_resource');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->text('description')->nullable();
            $table->text('phone_number')->nullable();
            $table->integer('phone_code')->nullable();
            $table->integer('age')->nullable();
            $table->boolean('is_casting')->default(false);
            $table->enum('gender', ['MALE', 'FEMALE']);
            $table->unsignedBigInteger('country_id')->nullable();
            $table->foreign('country_id')
                ->references('id')
                ->on('countries')->onDelete('cascade');


            $table->unsignedBigInteger('profile_image')->nullable();
            $table->foreign('profile_image')
                ->references('id')
                ->on('resources')->onDelete('cascade');

            $table->unsignedBigInteger('resume_file')->nullable();
            $table->foreign('resume_file')
                ->references('id')
                ->on('resources')->onDelete('cascade');

            $table->unsignedBigInteger('profession_id')->nullable();
            $table->foreign('profession_id')
                ->references('id')
                ->on('professions')->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
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
        Schema::dropIfExists('user_profiles');
    }
}

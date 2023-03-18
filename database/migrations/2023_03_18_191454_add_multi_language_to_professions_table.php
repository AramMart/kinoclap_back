<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiLanguageToProfessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('professions', function (Blueprint $table) {
            $table->renameColumn('name', 'name_am');
            $table->string('name_ru')->after('name');
            $table->string('name_en')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('professions', function (Blueprint $table) {
            $table->renameColumn('name_am', 'name');
            $table->dropColumn('name_ru');
            $table->dropColumn('name_en');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultiLanguageToNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->renameColumn('title', 'title_am');
            $table->string('title_ru')->after('title');
            $table->string('title_en')->after('title');
            $table->renameColumn('description', 'description_am');
            $table->text('description_ru')->after('description');
            $table->text('description_en')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('news', function (Blueprint $table) {
            $table->renameColumn('title_am', 'title');
            $table->dropColumn('title_ru');
            $table->dropColumn('title_en');
            $table->renameColumn('description_am', 'description');
            $table->dropColumn('description_ru');
            $table->dropColumn('description_en');
        });
    }
}

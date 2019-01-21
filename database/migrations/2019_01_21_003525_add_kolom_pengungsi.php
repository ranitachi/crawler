<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKolomPengungsi extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('berita_result', function (Blueprint $table) {
            $table->integer('jlh_pengungsi')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('berita_result', function (Blueprint $table) {
            $table->dropColumn('jlh_pengungsi');
        });
    }
}

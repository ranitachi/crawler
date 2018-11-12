<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeritaCrawlersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('berita_crawler', function (Blueprint $table) {
            $table->increments('id');
            $table->string('portal_id')->nullable()->default(0);
            $table->string('url')->nullable();
            $table->string('file')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('judul')->nullable();
            $table->longText('isi')->nullable();
            $table->timestamps();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('berita_crawler');
    }
}

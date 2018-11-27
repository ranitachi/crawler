<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBeritaResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('berita_result', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_berita')->nullable()->default(0);
            $table->integer('kategori')->nullable()->default(0);
            $table->integer('provinsi')->nullable()->default(0);
            $table->integer('kabupaten')->nullable()->default(0);
            $table->string('lokasi')->nullable();
            $table->integer('tanggal_kejadian')->nullable()->default(0);
            $table->integer('meninggal')->nullable()->default(0);
            $table->integer('luka')->nullable()->default(0);
            $table->integer('bangunan_rusak')->nullable()->default(0);
            $table->string('url_berita')->nullable();
            $table->string('judul')->nullable();
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
        Schema::drop('berita_result');
    }
}

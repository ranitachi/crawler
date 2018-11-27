<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagingSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paging_setting', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->nullable()->default(0);
            $table->string('tag')->nullable();
            $table->string('name')->nullable();
            $table->string('html')->nullable();
            $table->integer('count_of_page')->nullable()->default(0);
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
        Schema::drop('paging_setting');
    }
}

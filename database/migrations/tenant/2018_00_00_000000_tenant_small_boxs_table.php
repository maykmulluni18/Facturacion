<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantSmallBoxsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//
        /*
         ***************************************************************************************************************
         ***************************************************************************************************************
         ***************************************************************************************************************
         */
        Schema::create('small_boxs', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('description_movement');
            $table->string('type_movement');
            $table->timestamp('date_movement');
            $table->float('amount_movement',10,2);
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
        Schema::dropIfExists('small_boxs');

    }
}

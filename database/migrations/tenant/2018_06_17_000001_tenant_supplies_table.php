<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantSuppliesTable extends Migration
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
        Schema::create('supplies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('second_name')->nullable();
            $table->float('costs_unit',10,2);
            $table->float('quantity',10,2);
            $table->string('unit');
            $table->integer('category_supplies');
            $table->integer("item_id");
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
        Schema::dropIfExists('supplies');

    }
}

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantRecipesCostsTable extends Migration
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
        Schema::create('recipes_subrecipes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->float('sale_price')->default(0);
            $table->string('type_doc');
            $table->float('quantity',10,2);
            $table->text('subrecipes_supplies');
            $table->text('cif');
            $table->text('costs');
            $table->unsignedInteger("item_id")->nullable()->unsigned();
            $table->timestamps();
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');

        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipes_subrecipes');

    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantContractDetailsItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_details_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger("contract_items_id");
            $table->string("imageurl")->nullable();
            $table->string("tematica")->nullable();
            $table->string("details")->nullable();
            $table->foreign('contract_items_id')->references('id')->on('contract_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_details_items');
        
    }
}

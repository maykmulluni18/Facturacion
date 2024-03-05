<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenantRatiosFinanceTable extends Migration
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
        Schema::create('ratios_general', function (Blueprint $table) {
            $table->increments('id');
            $table->float('ratio_tesoreria');
            $table->text('ratio_tesoreria_formula');
            $table->float('ratio_liquidez');
            $table->text('ratio_liquidez_formula');
            $table->float('ratio_rentabilidad_cap_total',10);
            $table->text('ratio_rentabilidad_cap_total_formula');
            // $table->float('ratio_rentabilidad_general');
            // $table->text('ratio_rentabilidad_general_formula');
            // $table->float('ratio_rentabilidad_ventas');
            // $table->text('ratio_rentabilidad_ventas_formula');
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
        Schema::dropIfExists('ratios_general');

    }
}

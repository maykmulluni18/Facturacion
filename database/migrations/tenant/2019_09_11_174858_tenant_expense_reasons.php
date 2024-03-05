<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class TenantExpenseReasons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expense_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
        });

        
        DB::table('expense_reasons')->insert([
            ['id' => '1', 'description' => 'Varios'],
            ['id' => '2', 'description' => 'Representación de la organización'],
            ['id' => '3', 'description' => 'Trabajo de campo'], 
            // Gastos Operativos Fijos
            ['id' => '4', 'description' => 'Alquiler de Planta'], 
            ['id' => '5', 'description' => 'Agua - Planta'], 
            ['id' => '6', 'description' => 'Luz - planta'], 
            ['id' => '7', 'description' => 'Mano de Obra Directa Fija'], 
            ['id' => '8', 'description' => 'Supervisor de Planta'], 
            ['id' => '9', 'description' => 'Plan Movil'], 
            ['id' => '10', 'description' => 'Internet'], 
            ['id' => '11', 'description' => 'Seguros de Planta'], 
            ['id' => '12', 'description' => 'Mercaderia de Empaque'], 
            // Gastos Operativos Variables
            ['id' => '13', 'description' => 'Viáticos por viaje'], 
            ['id' => '14', 'description' => 'CAJA CHICA'], 
            ['id' => '15', 'description' => 'Transporte de Compras'], 
            ['id' => '16', 'description' => 'Transporte Delivery'], 
            ['id' => '17', 'description' => 'Mano de Obra Tercerizada'], 
            // Gastos de Venta
            ['id' => '18', 'description' => 'Alquiler de Local Comercial'], 
            ['id' => '19', 'description' => 'Planilla Comercial'], 
            ['id' => '20', 'description' => 'Gastos de Representacion'], 
            ['id' => '21', 'description' => 'Movilidad - Venta'], 
            ['id' => '22', 'description' => 'Comisiones - Venta'], 
            ['id' => '23', 'description' => 'Publicidad'], 
            // Gastos de Administracion
            ['id' => '24', 'description' => 'Alquiler de Local Administrativo'], 
            ['id' => '25', 'description' => 'Plan Movil'], 
            ['id' => '26', 'description' => 'Agua - Oficinas'], 
            ['id' => '27', 'description' => 'Luz - Oficinas'], 
            ['id' => '28', 'description' => 'Utiles de Aseo y Oficina'], 
            ['id' => '29', 'description' => 'Movilidad Administrativa'], 
            ['id' => '30', 'description' => 'Gerente'], 
            ['id' => '31', 'description' => 'Administrador'], 
            ['id' => '32', 'description' => 'Auxiliar Administrativo'], 
            ['id' => '33', 'description' => 'Jefe de Operaciones'], 
            ['id' => '34', 'description' => 'Supervisores de Areas'], 
            ['id' => '35', 'description' => 'Supervisor de Mantenimiento'], 
            ['id' => '36', 'description' => 'Contador'], 
            ['id' => '37', 'description' => 'Asesor Externo'], 
            // Gasto Financiero
            ['id' => '38', 'description' => 'Comis. Mante. Ctas. de Banco'], 
            ['id' => '39', 'description' => 'Cuota de prestamos'], 
            ['id' => '40', 'description' => 'Comis. Pasarelas de Pago'], 
            // Inversion al Activo
            ['id' => '41', 'description' => 'Compra de Equipos y Maquinarias'],
            ['id' => '42', 'description' => 'Implementacion o Arreglos'], 
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expense_reasons');        
    }
}

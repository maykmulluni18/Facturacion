<?php

namespace Modules\Finance\Http\Controllers;

use App\Models\Tenant\Cash;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Exports\MovementExport;
use Modules\Finance\Models\IndirectExpense;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Pos\Models\CashTransaction;
use App\Models\Tenant\DownloadTray;
use Modules\Finance\Jobs\ProcessMovementsReport;
use App\Models\System\Client;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\RecipesSubrecipe;
use App\Models\Tenant\SaleNoteItem;
use App\Http\Resources\Tenant\DocumentCollection;
use App\Models\Tenant\Document;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Models\Tenant\SaleNote;

class BreakPointMonthController extends Controller
{

    use FinanceTrait;

    public function index()
    {

        $isMovements = 1;
        return view('finance::movements.index', compact('isMovements'));
    }

    public function indexTransactions()
    {

        $isMovements = 0;
        return view('finance::movements.index', compact('isMovements'));
    }
    public function indexFlowfinance()
    {

        $isMovements = 0;
        return view('finance::flowfinance.index', compact('isMovements'));
    }
    public function indexBreakpointmonth()
    {
        $isMovements = 0;
        return view('finance::breakpointmonth.index', compact('isMovements'));
    }
    public function indexStatewinlose()
    {
        $isMovements = 0;
        return view('finance::statewinlose.index', compact('isMovements'));
    }

    public function update(Request $request){
        $params = $request->all();
        IndirectExpense::query()->updateOrCreate(['id' => $params['id']],$params);
        return ["success"=>true,"message"=>"Registro Actualizado con exito"];
    }
    public function getSumaGastosIndirectosMensual($items){
        $total = 0 ;
        for ($i=0; $i < count($items) ; $i++) { 
            $total+=$items[$i]['amount'];

        }
        return $total;
    }
    public function records(Request $request)
    {
        $records = $this->getRecords($request->all(),IndirectExpense::class)->all();

        if(count($records) == 0){
            // le vamos a insertar
            $this->storeWithDataInit();
            $records = $this->getRecords($request->all(),IndirectExpense::class)->all();            
        }
        $suma_gastos_indirectos_mensual =$this->getSumaGastosIndirectosMensual($records);

        $records_recipe = RecipesSubrecipe::query()->where('type_doc',"=","recipe")->get();
        $margen_contribucion_promedio_negocio = $this->getPorcentageAverageMargin($records_recipe);

        $records_comprobante_electronico = $this->recordsDocumentMesAnterior($request); // para los ingresos document
        $records_sales_note = $this->recordsSaleNoteMesAnterior($request) ; // para sales note

        $porcentaje_ventas_mes_pasado= $this->calculatePorcentageTotalIngresos($records_sales_note,$records_comprobante_electronico);


        $records_sales_note_mes_actual = $this->recordsVentasSaleNoteMesActual($request) ; // para sales note mes actual
        $records_comprobatnte_electronico_mes_actual = $this->recordsVentasDocumentsMesActual($request) ; // para comprobante electronico

        $promedio_total_venta_mes_actual = $this->calculatePorcentageTotalIngresos($records_sales_note_mes_actual,$records_comprobatnte_electronico_mes_actual);

        $venta_bruta_mensual = $margen_contribucion_promedio_negocio != 0? $suma_gastos_indirectos_mensual / $margen_contribucion_promedio_negocio :0;
        $numero_ventas_promedio = $porcentaje_ventas_mes_pasado["promedio"] == 0?0: $venta_bruta_mensual/$porcentaje_ventas_mes_pasado["promedio"];
        $numero_ventas_promedio_2 = $porcentaje_ventas_mes_pasado["promedio"] == 0?0: $promedio_total_venta_mes_actual['total_bruto']/$porcentaje_ventas_mes_pasado["promedio"];
        return [
            "venta_bruta_mensual" =>$venta_bruta_mensual ,
            "numero_ventas_promedio"=>$numero_ventas_promedio,

            "venta_bruta_2" =>$promedio_total_venta_mes_actual['total_bruto'],
            "numero_ventas_promedio_2"=>$numero_ventas_promedio_2,

            "venta_bruta_3" => $venta_bruta_mensual - $promedio_total_venta_mes_actual['total_bruto'],
            "numero_ventas_promedio_3"=>$numero_ventas_promedio - $numero_ventas_promedio_2,
            "ventas_totales"=>$porcentaje_ventas_mes_pasado['total_bruto'],
            "records"=>$records
            
            // "porcentage_average_recipes"=>$records_recipe,
            // "porcentaje_ventas_mes_pasado"=>$porcentaje_ventas_mes_pasado["promedio"],
            // "total_bruto"=>$porcentaje_ventas_mes_pasado["total_bruto"],
            // "total_ventas_hasta_mes_actual"=>$promedio_total_venta_mes_actual["total"],
        ];
    }
    public function calculatePorcentageTotalIngresos($sale_note,$comprobante_electronico){
        $total_sales = 0;
        $total_register = count($sale_note) + count($comprobante_electronico);
        for ($i=0; $i < count($sale_note) ; $i++) { 
            $total_sales=$total_sales+$sale_note[$i]['total'];
        }
        for ($i=0; $i < count($comprobante_electronico) ; $i++) { 
            $total_sales=$total_sales+$comprobante_electronico[$i]['total'];
        }
        $promedio = $total_register == 0?0 :$total_sales / $total_register;
        return ["promedio"=> $promedio ,"total_bruto"=>$total_sales] ;
    }
    public function storeWithDataInit(){
        $values = [
            "ALQUILERES","ASESORIA Y CONSULTORIA","CONSTRUCCION, REMODELACION","DEUDAS Y PRESTAMOS",
            "IMPUESTOS","INTERNET Y TELEFONO","LABOR INDIRECTA (SALARIOS Y BENEFICIOS)","LIMPIEZA Y MANTENIMIENTO",
            "MARKETING Y PUBLICIDAD","OTROS GASTOS INDIRECTOS","PAGOS DE SERVICIOS","RECREACION Y ENTRETENIMIENTO",
            "SEGUROS","UTILES Y SUMINISTROS DE OFICINA","INTERNET Y TELEFONO","TECNOLOGIA","RECREACION Y ENTRETENIMIENTO",
            "VIAJES","OTROS GASTOS INDIRECTOS","IMPUESTOS"
        ];
        DB::connection('tenant')->beginTransaction();
        for ($i=0; $i < count($values) ; $i++) { 
            $data = [
                'id'=>null,
                'name'=>$values[$i],
                'amount'=>0,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s"),
            ];
            IndirectExpense::query()->updateOrCreate(['id' => $data['id']], $data);
            DB::connection('tenant')->commit();
        }
    }
    public function getPorcentageAverageMargin($records_recipe){
        $total = 0;
        $num_items = 0;
        for ($i=0; $i < count($records_recipe) ; $i++) { 
            $item = json_decode($records_recipe[$i]['costs'],true);
            $total=$total + $item['margin_costs_procentage'];
            $item++;
            $num_items++;
        }
        if( $num_items == 0) return 0 ;
        else return ($total / $num_items)/100 ;
    }
    // para los ingresos por comprobante electronico
    public function recordsDocumentMesAnterior(){
        $date_start = date("Y-m-d",strtotime(date("Y-m-d") . "-2 month")); 
        $date_end = date("Y-m-d",strtotime(date("Y-m-d") . "-1 month")); 
        $records = Document::query()->whereBetween('created_at',[$date_start . " 00:00:00",$date_end . " 23:59:59"])->get();
        return $records ;
    }

    //  fin para los ingresos de comprobante electronico

    // para las notas de salida - con la nota de venta 
    public function recordsSaleNoteMesAnterior()
    {
        $date_start = date("Y-m-d",strtotime(date("Y-m-d") . "-2 month")); 
        $date_end = date("Y-m-d",strtotime(date("Y-m-d") . "-1 month")); 
        $records = SaleNote::query()->whereBetween('created_at',[$date_start . " 00:00:00",$date_end . " 23:59:59"])->get();
        return $records;
    }
    
    public function recordsVentasSaleNoteMesActual()
    {
        $date_end = date("Y-m-d");
        $date_start = date("Y-m"); 
        $records = SaleNote::query()->whereBetween('created_at',[$date_start . "-1 00:00:00",$date_end . " 23:59:59"])->get();
        return $records;

    }
    
    private function recordsVentasDocumentsMesActual(){
        $date_end = date("Y-m-d");
        $date_start = date("Y-m"); 
        $records = Document::query()->whereBetween('created_at',[$date_start . "-1 00:00:00",$date_end . " 23:59:59"])->get();
        return $records;
    }
    // fin para las notas de salida - con la nota de venta 
    /**
     * @param array $request
     * @param  GlobalPayment::class  $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRecords($request, $model)
    {        
        $records = $model::select('id','name','amount');
        $records = $records->get();
        return $records;
    }


    public function pdf(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = $this->getRecords($request->all(), GlobalPayment::class)->get();

        $pdf = PDF::loadView('finance::movements.report_pdf', compact("records", "company", "establishment"))->setPaper('a4', 'landscape');;

        $filename = 'Reporte_Movimientos_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }


    public function postPdf(Request $request)
    {
        $records = $request->data;
        $order = $request->order;
        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;

        $pdf = PDF::loadView(
            'finance::movements.new_report_pdf',
            compact('records', 'company', 'establishment')
        )
            ->setPaper('a4', 'landscape');;


        $filename = 'Reporte_Movimientos_' . date('YmdHis');

        return $pdf->download($filename . '.pdf');
    }

    public function postExcel(Request $request)
    {

        $company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id)
            : auth()->user()->establishment;
        $records = $request->data;
        $MovementExport = new MovementExport();
        $MovementExport
            ->records($records)
            ->company($company)
            ->setNewFormat(true)
            ->establishment($establishment);

        return $MovementExport->download('Reporte_Movimientos_' . Carbon::now() . '.xlsx');
    }
    public function excel(Request $request)
    {

        /*$params = (object) array_merge( $request->all(), ['user_id' => auth()->user()->id]);
        return json_encode($params);*/

        $company = Company::active();
        $client = Client::where('number', $company->number)->first();
        $website_id = $client->hostname->website_id;

        // $records = $this->getRecords($request->all(), GlobalPayment::class);
        //$records->orderBy('id');

        $tray = DownloadTray::create([
            'user_id' => auth()->user()->id,
            'module' => 'INVENTORY',
            'path' => $request->path,
            'format' => 'xlsx',
            'date_init' => date('Y-m-d H:i:s'),
            'type' => 'Reporte Movimientos ingresos-egresos'
        ]);

        $params = (object)array_merge($request->all(), ['user_id' => auth()->user()->id, 'type' => auth()->user()->type, 'establishment_id' => auth()->user()->establishment_id]);

        ProcessMovementsReport::dispatch($params, $tray->id, $website_id);
        // ProcessMovementsReport::dispatch($params, $tray->id, $website_id)->onQueue('process_movements_report');

        return [
            'success' => true,
            'message' => 'El reporte se esta procesando; puede ver el proceso en bandeja de descargas.'
        ];

        /*$company = Company::first();
        $establishment = ($request->establishment_id) ? Establishment::findOrFail($request->establishment_id) : auth()->user()->establishment;
        $records = $this->getRecords($request->all(), GlobalPayment::class)->get();

        $movementExport  = new MovementExport();
        $movementExport
            ->records($records)
            ->company($company)
            ->establishment($establishment);
        return $movementExport->view();
        return $movementExport->download('Reporte_Movimientos_'.Carbon::now().'.xlsx');*/
    }
}

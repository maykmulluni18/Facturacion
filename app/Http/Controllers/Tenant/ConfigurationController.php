<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\Tenant\ConfigurationRequest;
use App\Http\Resources\Tenant\ConfigurationResource;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Item;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Tenant\Catalogs\{
    AffectationIgvType,
    ChargeDiscountType
};
use GuzzleHttp\Client;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Modules\Finance\Traits\FinanceTrait;
use App\CoreFacturalo\Template;
use App\Models\Tenant\Company;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\FormatTemplate;
use Modules\LevelAccess\Models\ModuleLevel;
use Validator;
use App\Models\Tenant\Skin;
use Modules\Finance\Helpers\UploadFileHelper;
use App\Models\Tenant\BankAccount;
use Modules\Dashboard\Helpers\DashboardView;
use App\Models\Tenant\Document;
use App\Models\Tenant\Supplie;
use App\Models\Tenant\BreakpointLog;
use App\Models\Tenant\RatiosFinance;
use App\Models\Tenant\RecipesSubrecipe;
use App\Models\Tenant\RecipesSubrecipeLog;
use App\Models\Tenant\SuppliesLog;
use Modules\Finance\Helpers\ToPay;
use Modules\Finance\Http\Resources\UnpaidCollection;
use Modules\Finance\Models\GlobalPayment;
use App\Models\Tenant\Cash;
use App\Models\Tenant\SaleNote;


class ConfigurationController extends Controller
{
    use FinanceTrait;

    public function create()
    {
        return view('tenant.configurations.form');
    }

    public function generateDispatch(Request $request)
    {
        $template = new Template();
        $pdf = new Mpdf();
        $pdf_margin_top = 15;
        $pdf_margin_bottom = 15;
        // $pdf_margin_top = 15;
        $pdf_margin_right = 15;
        // $pdf_margin_bottom = 15;
        $pdf_margin_left = 15;

        $pdf_font_regular = config('tenant.pdf_name_regular');
        $pdf_font_bold = config('tenant.pdf_name_bold');

        if ($pdf_font_regular != false) {
            $defaultConfig = (new ConfigVariables())->getDefaults();
            $fontDirs = $defaultConfig['fontDir'];

            $defaultFontConfig = (new FontVariables())->getDefaults();
            $fontData = $defaultFontConfig['fontdata'];

            $pdf = new Mpdf([
                'fontDir' => array_merge($fontDirs, [
                    app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                                             DIRECTORY_SEPARATOR . 'pdf' .
                                             DIRECTORY_SEPARATOR . $base_pdf_template .
                                             DIRECTORY_SEPARATOR . 'font')
                ]),
                'fontdata' => $fontData + [
                    'custom_bold' => [
                        'R' => $pdf_font_bold . '.ttf',
                    ],
                    'custom_regular' => [
                        'R' => $pdf_font_regular . '.ttf',
                    ],
                ],
                'margin_top'    => $pdf_margin_top,
                'margin_right'  => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left'   => $pdf_margin_left,
            ]);
        } else {
            $pdf = new Mpdf([
                'margin_top'    => $pdf_margin_top,
                'margin_right'  => $pdf_margin_right,
                'margin_bottom' => $pdf_margin_bottom,
                'margin_left'   => $pdf_margin_left
            ]);
        }
        $path_css = app_path('CoreFacturalo' . DIRECTORY_SEPARATOR . 'Templates' .
                                             DIRECTORY_SEPARATOR . 'preprinted_pdf' .
                                             DIRECTORY_SEPARATOR . $request->base_pdf_template .
                                             DIRECTORY_SEPARATOR . 'style.css');

        $stylesheet = file_get_contents($path_css);

        // $actions = array_key_exists('actions', $request->inputs)?$request->inputs['actions']:[];
        $actions = [];
        $html = $template->preprintedpdf($request->base_pdf_template, 'dispatch', Company::active(), 'a4');
        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        Storage::put('preprintedpdf' . DIRECTORY_SEPARATOR . $request->base_pdf_template . '.pdf', $pdf->output('', 'S'));

        return $request->base_pdf_template;
    }

    public function show($template)
    {
        return response()->file(storage_path('app' . DIRECTORY_SEPARATOR . 'preprintedpdf' . DIRECTORY_SEPARATOR . $template . '.pdf'));
    }

    // public function dispatch(Request $request) {
    //     dd($request);
    //     return 'prueba';

    //     $fact = DB::connection('tenant')->transaction(function () use($request) {
    //         $facturalo = new Facturalo();
    //         $facturalo->save($request->all());
    //         $facturalo->createXmlUnsigned();
    //         $facturalo->signXmlUnsigned();
    //         $facturalo->createPdf();
    //         $facturalo->senderXmlSignedBill();

    //         return $facturalo;
    //     });

    //     $document = $fact->getDocument();
    //     $response = $fact->getResponse();

    //     return [
    //         'success' => true,
    //         'message' => "Se creo la guía de remisión {$document->series}-{$document->number}",
    //         'data' => [
    //             'id' => $document->id,
    //         ],
    //     ];
    // }

    public function addSeeder()
    {
        $reiniciar = DB::connection('tenant')
                        ->table('format_templates')
                        ->truncate();
        $archivos = Storage::disk('core')->allDirectories('Templates/pdf');
        $collection = [];
        $valor = [];
        foreach ($archivos as $valor) {
            $line = explode('/', $valor);
            if (count($line) <= 3) {
                array_push($collection, $line);
            }
        }

        foreach ($collection as $insertar) {
            $urls = [
                'guide' => \File::exists(public_path('templates/pdf/'.$insertar[2].'/image_guide.png')) ? 'templates/pdf/'.$insertar[2].'/image_guide.png' : '',
                'invoice' => \File::exists(public_path('templates/pdf/'.$insertar[2].'/image.png')) ? 'templates/pdf/'.$insertar[2].'/image.png' : 'templates/pdf/default/image.png',
                'ticket' => \File::exists(public_path('templates/pdf/'.$insertar[2].'/ticket.png')) ? 'templates/pdf/'.$insertar[2].'/ticket.png' : '',
            ];

            $insertar = DB::connection('tenant')
            ->table('format_templates')
            ->insert([
                [
                    'formats' => $insertar[2],
                    'urls' => json_encode($urls),
                    'is_custom_ticket' => \File::exists(public_path('templates/pdf/'.$insertar[2].'/ticket.png')) ? 1 : 0 ]
            ]);
        }

        // revisión custom //obsoleto
        // $exists = Storage::disk('core')->exists('Templates/pdf/custom/style.css');
        // if (!$exists) {
        //     Storage::disk('core')->copy('Templates/pdf/default/style.css', 'Templates/pdf/custom/style.css');
        //     Storage::disk('core')->copy('Templates/pdf/default/invoice_a4.blade.php', 'Templates/pdf/custom/invoice_a4.blade.php');
        //     Storage::disk('core')->copy('Templates/pdf/default/partials/footer.blade.php', 'Templates/pdf/custom/partials/footer.blade.php');
        // }

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function refreshTickets()
    {
        $lists = FormatTemplate::where('is_custom_ticket', true)->get();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function getTicketFormats()
    {
        $formats = FormatTemplate::where('is_custom_ticket', true)->get()->transform(function($row) {
                return $row->getCollectionData();
        });

        return compact('formats');
    }

    public function addPreprintedSeeder()
    {
        $reiniciar = DB::connection('tenant')
                        ->table('preprinted_format_templates')
                        ->truncate();
        $archivos = Storage::disk('core')->allDirectories('Templates/preprinted_pdf');
        $colection = [];
        $valor = [];
        foreach ($archivos as $valor) {
            $lina = explode('/', $valor);
            if (count($lina) <= 3) {
                array_push($colection, $lina);
            }
        }

        foreach ($colection as $insertar) {
            $insertar = DB::connection('tenant')
            ->table('preprinted_format_templates')
            ->insert(['formats' => $insertar[2]]);
        }

        // revisión custom
        $exists = Storage::disk('core')->exists('Templates/preprinted_pdf/custom/style.css');
        if (!$exists) {
            Storage::disk('core')->copy('Templates/preprinted_pdf/default/style.css', 'Templates/preprinted_pdf/custom/style.css');
            Storage::disk('core')->copy('Templates/preprinted_pdf/default/invoice_a4.blade.php', 'Templates/preprinted_pdf/custom/invoice_a4.blade.php');
            Storage::disk('core')->copy('Templates/preprinted_pdf/default/partials/footer.blade.php', 'Templates/preprinted_pdf/custom/partials/footer.blade.php');
        }

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function changeFormat(Request $request)
    {
        $establishment = Establishment::find($request->establishment);
        $establishment->template_pdf = $request->formats;
        $establishment->save();

        // $config_format = config(['tenant.pdf_template' => $format->formats]);
        // $fp = fopen(base_path() .'/config/tenant.php' , 'w');
        // fwrite($fp, '<?php return ' . var_export(config('tenant'), true) . ';');
        // fclose($fp);
        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function changeTicketFormat(Request $request)
    {
        $establishment = Establishment::find($request->establishment);
        $establishment->template_ticket_pdf = $request->formats;
        $establishment->save();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function getFormats()
    {
        $formats = FormatTemplate::get()->transform(function($row) {
                return $row->getCollectionData();
        });

        return compact('formats');

        return $formats;
    }

    public function getPreprintedFormats()
    {
        $formats = DB::connection('tenant')->table('preprinted_format_templates')->get();

        return $formats;
    }

    public function pdfTemplates()
    {
        $establishments = Establishment::select(['id','description','template_pdf'])->get();
        return view('tenant.advanced.pdf_templates')->with('establishments', $establishments);
    }

    public function pdfTicketTemplates()
    {
        $establishments = Establishment::select(['id','description','template_ticket_pdf'])->get();
        return view('tenant.advanced.pdf_ticket_templates')->with('establishments', $establishments);
    }

    public function pdfGuideTemplates()
    {
        return view('tenant.advanced.pdf_guide_templates');
    }

    public function pdfPreprintedTemplates()
    {
        return view('tenant.advanced.pdf_preprinted_templates');
    }
    public function getBreakPoints(){
        $records = $this->getRecords(IndirectExpense::class)->all();

        $suma_gastos_indirectos_mensual = $this->getSumaGastosIndirectosMensual($records);

        $records_recipe = RecipesSubrecipe::whereTypeUser()->where('type_doc',"=","recipe")->get();
        $margen_contribucion_promedio_negocio = $this->getPorcentageAverageMargin($records_recipe);

        $records_comprobante_electronico = $this->recordsDocumentMesAnterior(); // para los ingresos document
        $records_sales_note = $this->recordsSaleNoteMesAnterior() ; // para sales note

        $porcentaje_ventas_mes_pasado= $this->calculatePorcentageTotalIngresos($records_sales_note,$records_comprobante_electronico);


        $records_sales_note_mes_actual = $this->recordsVentasSaleNoteMesActual() ; // para sales note mes actual
        $records_comprobatnte_electronico_mes_actual = $this->recordsVentasDocumentsMesActual() ; // para comprobante electronico

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
        $records = SaleNote::whereTypeUser()->whereBetween('created_at',[$date_start . " 00:00:00",$date_end . " 23:59:59"])->get();
        return $records;
    }
    
    public function recordsVentasSaleNoteMesActual()
    {
        $date_end = date("Y-m-d");
        $date_start = date("Y-m"); 
        $records = SaleNote::whereTypeUser()->whereBetween('created_at',[$date_start . "-1 00:00:00",$date_end . " 23:59:59"])->get();
        return $records;

    }
    
    private function recordsVentasDocumentsMesActual(){
        $date_end = date("Y-m-d");
        $date_start = date("Y-m"); 
        $records = Document::whereTypeUser()->whereBetween('created_at',[$date_start . "-1 00:00:00",$date_end . " 23:59:59"])->get();
        return $records;
    }
    // fin para las notas de salida - con la nota de venta 
    /**
     * @param array $request
     * @param  GlobalPayment::class  $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRecords( $model)
    {        
        $records = $model::select('id','name','amount');
        $records = $records->get();
        return $records;
    }
    public function record()
    {
        //  aca poner que guarde los calculos de ratios diario
        $day_for_break_point = date('d');
        if($day_for_break_point == '28'){
            $breakpoints = $this->getBreakPoints();
            $params = ["venta_bruta"=>$breakpoints["venta_bruta_2"],"date_reg"=>date("Y-m-d")];
            // save brak_points
            BreakpointLog::query()->updateOrCreate(['id' =>$params["id"] ], $params); 
        }
        $ratios = RatiosFinance::query()->where("created_at","like","%".date("Y-m-d") . "%")->get();
        if(count($ratios) == 0) $this->saveRatios();

        $configuration = Configuration::first();
        return ['data'=>$configuration->getCollectionData()];
        $record = new ConfigurationResource($configuration);

        return  $record;
    }
    public function saveRatios(){
        $records = RatiosFinance::query()->where("created_at","like", date("Y-m-d") . "%")->get();
        $records_all_accounts = $this->recordssumaSaldoTodasCuentas();
        $sum_all_banks = $this->getSumAccountsBank($records_all_accounts['records']);
        $request = [
            "customer_id"=>null,
            "date_end"=>date("Y-m-d"),
            "date_start"=>date("Y-m-d",strtotime(date("Y-m-d") ."- 1 month")),
            "establishment_id"=>1,
            "period"=>"between_dates"
        ];
        $cuentas_por_cobrar = $this->recordsCuentasPorCobrar($request);
        $request = [
            "customer_id"=>null,
            "date_end"=>date("Y-m-d"),
            "date_start"=>date("Y-m-d",strtotime(date("Y-m-d") ."- 1 month")),
            "establishment_id"=>1
        ];
        $cuentas_por_cobrar_alls = $this->recordsCuentasPorCobrar($request);
        $sum_cuentas_por_cobrar_alls = $this->getSumAccountsCobrarPagar($cuentas_por_cobrar_alls);
        $sum_cuentas_por_cobrar = $this->getSumAccountsCobrarPagar($cuentas_por_cobrar);
        //month_start:month_start, month_end:month_end,supplier_id:null,user:null};    
        $request = [
            "establishment_id"=>0,
            "period"=>"between_dates",
            "date_end"=>date("Y-m-d"),
            "date_start"=>date("Y-m-d",strtotime(date("Y-m-d") ."- 1 month")),
            "month_start"=>date("Y-m",strtotime(date("Y-m") ."- 1 month")),
            "month_end"=>date("Y-m")
        ];
        $cuentas_por_pagar = $this->cuentasPorPagar($request);
        $sum_cuentas_por_pagar = $this->getSumAccountsCobrarPagar($cuentas_por_pagar['records'],"cuent_cobrar");
        $ratios_tesoreria = $sum_cuentas_por_pagar != 0 ? ($sum_all_banks + $sum_cuentas_por_cobrar)/$sum_cuentas_por_pagar : 0;
        $request =[];
        $invetory_valuado_soles = $this->getInventoryValuedSol($request) ;
        $request = [
            "establishment_id"=>0,
            "period"=>"any",
            "date_end"=>date("Y-m-d"),
            "date_start"=>date("Y-m-d",strtotime(date("Y-m-d") ."- 1 month")),
            "month_start"=>date("Y-m",strtotime(date("Y-m") ."- 1 month")),
            "month_end"=>date("Y-m")
        ];
        $cuentas_por_pagar_alls = $this->cuentasPorPagar($request);
        $sum_accounts_to_pay_all = $this->getSumAccountsCobrarPagar($cuentas_por_pagar_alls['records'],"cuent_cobrar");
        $ratios_liquidez = $sum_accounts_to_pay_all != 0?($sum_all_banks + $sum_cuentas_por_cobrar_alls + $invetory_valuado_soles['data'] ):0 ;
        $ratios_rentabilidad_capital_total = ($sum_all_banks + $sum_accounts_to_pay_all) * 100;
        $request = ["date"=>date('Y-m-d')];
        $data = [
            "id"=> count($records) > 0 ? $records[0]['id'] : null,
            "ratio_tesoreria"=>round($ratios_tesoreria,3),
            "ratio_tesoreria_formula"=>"formula",
            "ratio_liquidez"=>round($ratios_liquidez,3),
            "ratio_liquidez_formula"=>"Formula",
            "ratio_rentabilidad_cap_total"=>(float)round($ratios_rentabilidad_capital_total,3),
            "ratio_rentabilidad_cap_total_formula"=>"formula"
        ];
        RatiosFinance::query()->updateOrCreate(['id' =>$data["id"] ], $data); 
    }
    public function getSumAccountsBank($items){
        $sum = 0;
        for ($i = 0; $i < count($items); $i++) {
            $element = $items[$i];
            $sum = $sum + (float) $element['balance'];
        }
        return $sum;
    }
    public function getSumAccountsCobrarPagar($inputs,$by = "no"){
        $sum = 0;
        for ($i = 0; $i < count($inputs); $i++) {
            $element = $inputs[$i];
            $sum = $sum + ( $by=="cuent_cobrar"?$element['total_to_pay'] : (float) $element->total_payment );
        }
        return $sum;
    }
    // cuentas por cobrar
    public function recordsCuentasPorCobrar( $request){
        $records = (new DashboardView())->getUnpaidFilterUser($request)->get();
        return $records;
    }
    // cuentas por pagar
    public function cuentasPorPagar($request){
        $data =$request;
        if($request['establishment_id'] === 0){
            $data['withBankLoan'] = 1;
            $data['stablishmentTopaidAll'] = 1; // Lista todos los establecimients
        }

        return [
            'records' => ToPay::getToPay($data)
       ];
    }
    // Suma de saldos de todas las cuentas 
    public function recordssumaSaldoTodasCuentas()
    {   
        return $this->setTotals([], $this->getRecordsSumaSaldoTodasCuentas());
    }
    public function setTotals($data = [], $record)
    {
        $data['records'] = $record;
        $data['totals'] = [];

        $data['totals']['t_initial_balance'] = $this::FormatNumber($record->sum('initial_balance'));
        $data['totals']['t_documents'] = $this::FormatNumber($record->sum('document_payment'));
        $data['totals']['t_sale_notes'] = $this::FormatNumber($record->sum('sale_note_payment'));
        $data['totals']['t_quotations'] = $this::FormatNumber($record->sum('quotation_payment'));
        $data['totals']['t_contracts'] = $this::FormatNumber($record->sum('contract_payment'));
        $data['totals']['t_technical_services'] = $this::FormatNumber($record->sum('technical_service_payment'));
        $data['totals']['t_income'] = $this::FormatNumber($record->sum('income_payment'));
        $data['totals']['t_expenses'] = $this::FormatNumber($record->sum('expense_payment'));
        $data['totals']['t_balance'] = $this::FormatNumber($record->sum('balance'));
        $data['totals']['t_purchases'] = $this::FormatNumber($record->sum('purchase_payment'));
        $data['totals']['t_bank_loan'] = $this::FormatNumber($record->sum('bank_loan'));
        $data['totals']['t_bank_loan_payment'] = $this::FormatNumber($record->sum('bank_loan_payment'));
        return $data;
    }
    public function getRecordsSumaSaldoTodasCuentas(){
        set_time_limit(3900);

            $data_of_period = ["d_start"=>"","d_end"=>""];

            $params = (object)[
                'date_start' => $data_of_period['d_start'],
                'date_end' => $data_of_period['d_end'],
            ];

            $bank_accounts = BankAccount::where('currency_type_id',"PEN")
            ->with(['global_destination' => function ($query) use ($params) {
                $query->whereFilterPaymentType($params);
            }])
                ->get();

            $all_cash = GlobalPayment::whereFilterPaymentType($params)
                ->with(['payment'])
                ->whereDestinationType(Cash::class)
                ->get();
            $balance_by_bank_acounts = $this->getBalanceByBankAcounts($bank_accounts,"PEN");
            $balance_by_cash = $this->getBalanceByCash($all_cash,"PEN");

            return $balance_by_bank_acounts->push($balance_by_cash);
    }

    public function getInventoryValuedSol($request){
        $records_supplies = Supplie::whereTypeUser()->get();
        $records_subrecipe = RecipesSubrecipe::whereTypeUser()->where('type_doc',"=","recipesub")->get();
        $records_recipe = RecipesSubrecipe::whereTypeUser()->where('type_doc',"=","recipe")->get();
        $sum_costs_supplies = $this->getInventoryValuedSolesSupplie($records_supplies);
        $sum_costs_subrecipe = $this->getInventoryValuedSolesSubRecipe($records_subrecipe);
        $sum_costs_recipe = $this->getInventoryValuedSolesRecipe($records_recipe);
        $resp = $sum_costs_supplies + $sum_costs_subrecipe + $sum_costs_recipe ;
        return ["data"=>$resp];
        // calcular el costo de cada uno en funcion a la cantidad que hay  en cada receta o sub receta
    }
    public function getInventoryValuedSolesSupplie($inputs){
        $resp = 0 ;
        for ($i=0; $i < count($inputs) ; $i++) { 
            $item = ($inputs[$i]['quantity'] * $inputs[$i]['costs_unit']) /1000;
            $resp = $resp + $item;
        }
        return $resp ;
    }
    public function getInventoryValuedSolesSubRecipe($inputs){
        $costs_item = 0; 
        $quantity = 0;
        $resp = 0 ;
        for ($i = 0; $i < count($inputs) ; $i++) { 
            $quantity_ = (float) $inputs[$i]['quantity'];
            $subrecipes_supplies = json_decode($inputs[$i]['subrecipes_supplies'],true);
            for ($j = 0; $j < count($subrecipes_supplies) ; $j++) {
                $costs_item = $costs_item + (float) $subrecipes_supplies[$j]['costs_by_grams'] ;
                $quantity = $quantity + (float) $subrecipes_supplies[$j]['quantity'] ;
            }
            $resp = ($costs_item * $quantity_)/$quantity ;
        }
        return $resp;
    }
    public function getInventoryValuedSolesRecipe($inputs){
        $costs_item = 0;
        $quantity = 0;
        $resp = 0;
        for ($i=0; $i < count($inputs) ; $i++) { 
            $quantity_ = (float) $inputs[$i]['quantity'];
            $subrecipes_supplies = json_decode($inputs[$i]['subrecipes_supplies'],true);
            for ($j=0; $j < count($subrecipes_supplies) ; $j++) { 
                $costs_item = $costs_item + (float) $subrecipes_supplies[$j]['costs_by_grams'] ;
                $quantity = $quantity + (float) $subrecipes_supplies[$j]['quantity'] ;
            }
            $resp = ($costs_item * $quantity_)/$quantity ;
        }
        return $resp;
    }
   
    
    public function getRecordsDocument($request){
        $total = 0;
        $records = Document::query()->whereTypeUser()->get();
        for ($i=0; $i < count($records) ; $i++) { 
            $total = $total + $records[$i]['total'];
        }
        return $total;
    }

    public function recordsSaleNote($request)
    {
        $total = $this->getRecordsSaleNote($request);   
        return $total;

    }
    private function getRecordsSaleNote($request){
        $total = 0;        
        $records = SaleNote::whereTypeUser()->get();
        for ($i=0; $i < count($records) ; $i++) { 
            $total = $total + $records[$i]['total'];
        }
        return $total;
    }
    public function store(ConfigurationRequest $request)
    {
        $id = $request->input('id');
        $configuration = Configuration::find($id);
        $configuration->fill($request->all());
        $configuration->save();


        return [
            'success' => true,
            'configuration' => $configuration->getCollectionData(),
            'message' => 'Configuración actualizada',
        ];
    }

    /**
     * Solo guarda lo sdatos de token para el cliente
     *
     * @param Request $request
     *
     * @return array
     */
    public function storeApiRuc( Request  $request)
    {
        $configuration = Configuration::first();
        if(empty($configuration)){
            $configuration = new Configuration();
        }
        $configuration->token_apiruc = $request->token_apiruc;
        $configuration->url_apiruc = $request->url_apiruc;

        $configuration->save();

        return [
            'success' => true,
            'configuration' => $configuration->getCollectionData(),
            'message' => 'Configuración actualizada',
        ];
    }

    public function icbper(Request $request)
    {
        DB::connection('tenant')->transaction(function () use ($request) {
            $id = $request->input('id');
            $configuration = Configuration::find($id);
            $configuration->amount_plastic_bag_taxes = $request->amount_plastic_bag_taxes;
            $configuration->save();

            $items = Item::get(['id', 'amount_plastic_bag_taxes']);

            foreach ($items as $item) {
                $item->amount_plastic_bag_taxes = $configuration->amount_plastic_bag_taxes;
                $item->update();
            }
        });

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function tables()
    {
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $global_discount_types = ChargeDiscountType::whereIn('id', ['02', '03'])->whereActive()->get();

        return compact('affectation_igv_types', 'global_discount_types');
    }

    public function visualDefaults()
    {
        $defaults = [
            'bg'       => 'light',
            'header'   => 'light',
            'sidebars' => 'light',
        ];
        $configuration = Configuration::first();
        $configuration->visual = $defaults;
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function visualSettings(Request $request)
    {
        $visuals = [
            'bg'       => $request->bg,
            'header'   => $request->header,
            'sidebars' => $request->sidebars,
            'navbar' => $request->navbar,
            'sidebar_theme' => $request->sidebar_theme
        ];

        $configuration = Configuration::find(1);
        $configuration->visual = $visuals;
        $configuration->save();

        return [
            'success' => true,
            'message' => 'Configuración actualizada'
        ];
    }

    public function getSystemPhone()
    {
        // $configuration = Configuration::first();
        // $ws = $configuration->enable_whatsapp;

        // $current = url('/phone');
        // $parse_current = parse_url($current);
        // $explode_current = explode('.', $parse_current['host']);
        // $app_url = config('app.url');
        // if(!array_key_exists('port', $parse_current)){
        //     $path = $app_url.$parse_current['path'];
        // }else{
        //     $path = $app_url.':'.$parse_current['port'].$parse_current['path'];
        // }

        // $http = new Client(['verify' => false]);
        // $response = $http->request('GET', $path);
        // if($response->getStatusCode() == '200'){
        //     $body = $response->getBody();

        //     $configuration->phone_whatsapp = $body;
        //     $configuration->save();
        // }
        // return 'error';
    }

    public function uploadFile(Request $request)
    {
        if ($request->hasFile('file')) {
            $configuration = Configuration::first();

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $name = date('Ymd') . '_' . $configuration->id . '.' . $ext;

            request()->validate(['file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048']);
            
            UploadFileHelper::checkIfValidFile($name, $file->getPathName(), true);

            $file->storeAs('public/uploads/header_images', $name);

            $configuration->header_image = $name;

            $configuration->save();

            return [
                'success' => true,
                'message' => __('app.actions.upload.success'),
                'name'    => $name,
            ];
        }

        return [
            'success' => false,
            'message' => __('app.actions.upload.error'),
        ];
    }

    public function changeMode()
    {
        $configuration = Configuration::first();
        $visual = $configuration->visual;
        $visual->sidebar_theme = $visual->bg === 'dark' ? 'white' : 'dark';
        $visual->bg = $visual->bg === 'dark' ? 'white' : 'dark';
        $configuration->visual = $visual;
        $configuration->save();

        return redirect()->back();
    }


    public function apiruc()
    {
        $configuration = Configuration::first();
        return [
            'url_apiruc' => $configuration->url_apiruc,
            'token_apiruc' => $configuration->token_apiruc,
            'token_false' => !$configuration->UseCustomApiPeruToken(),
        ];
    }

    private function getMenu() {
        $configuration = Configuration::first();
        return $menus = [
            'top_menu_a' => $configuration->top_menu_a_id ? $configuration->top_menu_a : '',
            'top_menu_b' => $configuration->top_menu_b_id ? $configuration->top_menu_b : '',
            'top_menu_c' => $configuration->top_menu_c_id ? $configuration->top_menu_c : '',
            'top_menu_d' => $configuration->top_menu_d_id ? $configuration->top_menu_d : '',
        ];
    }

    public function visualGetMenu()
    {
        $modules = ModuleLevel::where([['route_name', '!=', null],['label_menu', '!=', null]])->get();

        return [
            'modules' => $modules,
            'menu' => $this->getMenu()
        ];
    }

    public function visualSetMenu(Request $request)
    {
        $configuration = Configuration::first();
        $configuration->top_menu_a_id = $request->menu_a;
        $configuration->top_menu_b_id = $request->menu_b;
        $configuration->top_menu_c_id = $request->menu_c;
        $configuration->top_menu_d_id = $request->menu_d;
        $configuration->save();

        return [
            'success' => true,
            'menu' => $this->getMenu(),
            'message' => 'Configuración actualizada',
        ];
    }

    public function visualUploadSkin(Request $request)
    {
        if ($request->file->getClientMimeType() != 'text/css') {
            return [
                'success' => false,
                'message' =>  'Tipo de archivo no permitido',
            ];
        }
        if (Storage::disk('public')->exists('skins'.DIRECTORY_SEPARATOR.$request->file->getClientOriginalName())) {
            return [
                'success' => false,
                'message' =>  'Archivo ya existe',
            ];
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $file_content = file_get_contents($file->getRealPath());
            $filename = $file->getClientOriginalName();
            $name = pathinfo($file->getClientOriginalName());

            UploadFileHelper::checkIfValidCssFile($filename, $file->getPathName(), 'css', ['text/css', 'text/plain']);

            Storage::disk('public')->put('skins'.DIRECTORY_SEPARATOR.$filename, $file_content);

            $skin = new Skin;
            $skin->filename = $filename;
            $skin->name = $name['filename'];
            $skin->save();

            $skins = Skin::all();
            return [
                'success' => true,
                'message' =>  'Archivo cargado exitosamente',
                'skins' => $skins
            ];
        }
        return [
            'success' => false,
            'message' =>  __('app.actions.upload.error'),
        ];
    }

    public function visualDeleteSkin(Request $request)
    {
        $config = Configuration::first();
        if($config->skin_id == $request->id) {
            return [
                'success' => false,
                'message' => 'No se puede eliminar el Tema actual'
            ];
        }


        $skin = Skin::find($request->id);
        Storage::disk('public')->delete('skins'.DIRECTORY_SEPARATOR.$skin->filename);
        $skin->delete();

        $skins = Skin::all();

        return [
            'success' => true,
            'message' =>  'Tema eliminado correctamente',
            'skins' => $skins
        ];
    }
}

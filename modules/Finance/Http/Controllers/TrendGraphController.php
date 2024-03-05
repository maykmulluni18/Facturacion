<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Finance\Traits\FinanceTrait;
use App\Models\Tenant\RecipesSubrecipe;
use App\Models\Tenant\RecipesSubrecipeLog;
use App\Models\Tenant\Supplie;
use App\Models\Tenant\SuppliesLog;
use App\Http\Resources\Tenant\DocumentCollection;
use App\Models\Tenant\DocumentItem;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\Item;
use App\Models\Tenant\BreakpointLog;
use Modules\Expense\Models\ExpenseReason;
use Modules\Expense\Models\Expense;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNote;
use Modules\Expense\Models\ExpenseItem;




class TrendGraphController extends Controller
{

    use FinanceTrait;

    public function index()
    {

        $isMovements = 1;
        return view('finance::trendgraph.index', compact('isMovements'));
    }
    public function records(Request $request){
        $params = $request->all();
        $supplies = $this->recordsSuppliesTop($request);
        $d_start = $params['d_start']??null;
        $d_end = $params['d_end']??null;
        // ---------------- para obtener los 10 primeros productos mas vendidos ---------------------- 
        
        $records_sale_note_by_date = [] ;
        // ---------------- fin para obtener los 10 primeros productos mas vendidos ---------------------- 
        $records_sale_note_by_date_temp = SaleNoteItem::selectRaw('SUM(sale_note_items.quantity) AS cnt, sale_note_items.item_id ')
                                                ->join("sale_notes","sale_note_items.sale_note_id","=","sale_notes.id")
                                                ->whereBetween("sale_notes.created_at",[$d_start . " 00:00:00",$d_end . " 23:59:59"])
                                                ->groupBy("sale_note_items.item_id")
                                                ->orderBy("cnt","DESC")
                                                ->limit(5)
                                                ->get();

        $records_documents_by_date_temp = DocumentItem::selectRaw('SUM(document_items.quantity) AS cnt, document_items.item_id')
                                                ->join("documents","document_items.document_id","=","documents.id")
                                                ->whereBetween("documents.created_at",[$d_start . " 00:00:00",$d_end . " 23:59:59"])
                                                ->groupBy("document_items.item_id")
                                                ->orderBy("cnt","DESC")
                                                ->limit(5)
                                                ->get();
        for ($i=0; $i < count($records_sale_note_by_date_temp) ; $i++) { // obtener el nombre del producto mediante las nota de salidas
            $item = Item::query()->where("id","=",$records_sale_note_by_date_temp[$i]['item_id'])->get();
            $records_sale_note_by_date[$i]['amount'] = $records_sale_note_by_date_temp[$i]['cnt'] ;
            $records_sale_note_by_date[$i]['name_item'] = $item[0]['name'] . " " . $item[0]['second_name'] . " " . $item[0]['description'];
        }
        for ($i=0; $i < count($records_documents_by_date_temp) ; $i++) { // obtener el nombre del producto mediante las nota de salidas
            $item = Item::query()->where("id","=",$records_documents_by_date_temp[$i]['item_id'])->get();
            $records_sale_note_by_date[$i]['amount'] = $records_documents_by_date_temp[$i]['cnt'] ;
            $records_sale_note_by_date[$i]['name_item'] = $item[0]['name'] . " " . $item[0]['second_name'] . " " . $item[0]['description'];
        }
        $name_x = [];
        for ($i=0; $i < count($records_sale_note_by_date) ; $i++) { 
            $name_x[] = $records_sale_note_by_date[$i]['name_item'];
        } 
        $value_x = [];
        for ($i=0; $i < count($records_sale_note_by_date) ; $i++) { 
            $value_x[] = $records_sale_note_by_date[$i]['amount'];
        } 

        // --------------------
        $name_supp_x = [];
        for ($i=0; $i < count($supplies) ; $i++) { 
            $name_supp_x[] = $supplies[$i]['name_item'];
        } 
        $value_supp_x = [];
        for ($i=0; $i < count($supplies) ; $i++) { 
            $value_supp_x[] = $supplies[$i]['amount'];
        } 
        return [
            "productos_mas_vendidos"=>  [
                                            'totals' => [
                                                'total_documents' => number_format(count($records_sale_note_by_date),2, ".", ""),
                                                'total' => number_format( count($records_sale_note_by_date) ,2, ".", ""),
                                            ],
                                            'graph' => [
                                                'labels' => array_values($name_x),
                                                'datasets' => [
                                                    [
                                                        'label' => 'Productos mas Vendidos',
                                                        'data' => array_values($value_x),
                                                        'backgroundColor' => 'rgb(255, 99, 132)',
                                                        'borderColor' => 'rgb(255, 99, 132)',
                                                        'borderWidth' => 1,
                                                        'fill' => false,
                                                        'lineTension' => 0,
                                                    ]
                                                    // [
                                                    //     'label' => 'Insumos mas Vendidos',
                                                    //     'data' => array_values($supplies),
                                                    //     'backgroundColor' => 'rgb(201, 203, 207)',
                                                    //     'borderColor' => 'rgb(201, 203, 207)',
                                                    //     'borderWidth' => 1,
                                                    //     'fill' => false,
                                                    //     'lineTension' => 0,
                                                    // ]
                                                ],
                                            ]
                                        ],
            "insumos_mas_vendidos"=>    [
                                            'totals' => [
                                                'total_documents' => number_format(count($supplies),2, ".", ""),
                                                'total' => number_format( count($supplies) ,2, ".", ""),
                                            ],
                                            'graph' => [
                                                'labels' => array_values($name_supp_x),
                                                'datasets' => [
                                                    [
                                                        'label' => 'Insumos mas Vendidos',
                                                        'data' => array_values($value_supp_x),
                                                        'backgroundColor' => 'rgb(201, 203, 207)',
                                                        'borderColor' => 'rgb(201, 203, 207)',
                                                        'borderWidth' => 1,
                                                        'fill' => false,
                                                        'lineTension' => 0,
                                                    ]
                                                    
                                                ],
                                            ]
                                        ]
        ];
    }
    public function recordsSuppliesTop($request){
        $d_start = $request['d_start']??null;
        $d_end = $request['d_end']??null;
        $records_supplies_by_date = [];
        $records_supplies_by_date_temp = SuppliesLog::selectRaw('COUNT(supplies_id) AS cnt,supplies_id')
                                                ->whereBetween("created_at",[$d_start . " 00:00:00",$d_end . " 23:59:59"])
                                                ->groupBy("supplies_id")
                                                ->orderBy("cnt","DESC")
                                                ->limit(10)
                                                ->get();
        for ($i=0; $i < count($records_supplies_by_date_temp) ; $i++) { 
            $supplie = Supplie::query()->where("id","=",$records_supplies_by_date_temp[$i]['supplies_id'])->get();
            $records_supplies_by_date[$i]['amount'] = $records_supplies_by_date_temp[$i]['cnt'];
            $records_supplies_by_date[$i]['name_item'] = $supplie[0]['name'];
        }
        return $records_supplies_by_date;
    }
    public function recordsbreakpoint(Request $request){
        $params = $request->all();
        $breakpoints = BreakpointLog::query()->whereBetween("date_reg",[$params['d_start'] . " 00:00:00", $params['d_end'] . " 23:59:59" ])->get();
        $name_x = [];
        for ($i=0; $i < count($breakpoints) ; $i++) { 
            $date = explode("-",$breakpoints[$i]['date_reg']);
            $name_x[] = $date[0] . "-" . $date[1];
        }
        $value_x = [];
        for ($i=0; $i < count($breakpoints) ; $i++) { 
            $value_x[] = $breakpoints[$i]['venta_bruta'];
        }
        return [
            "breakpoints"=>  [
                        'totals' => [
                            'total_documents' => number_format(count($breakpoints),2, ".", ""),
                            'total' => number_format( count($breakpoints) ,2, ".", ""),
                        ],
                        'graph' => [
                            'labels' => array_values($name_x),
                            'datasets' => [
                                [
                                    'label' => 'Punto de Equilibrio',
                                    'data' => array_values($value_x),
                                    'backgroundColor' => 'rgb(43, 116, 180)',
                                    'borderColor' => 'rgb(43, 116, 180)',
                                    'borderWidth' => 1,
                                    'fill' => false,
                                    'lineTension' => 0,
                                ]
                            ],
                        ]
                    ]
        ];
    }
    public function recordsCategory(Request $request){
        $expense_reason = ExpenseReason::query()->get();
        return ["data"=>$expense_reason] ;
    }
    
    public function recordscategorybysales(Request $request){
        $params = $request->all();
        $exp_rz = ExpenseReason::query()->where("id" ,"=",$params['expense_id'] )->get()[0];
        $expense_reason[] = $exp_rz['description'];
        $expenses_items = Expense::query()->where("expense_reason_id","=",$params['expense_id'])->get();
        $expenses_reason = [];
        for ($i=0; $i < count($expenses_items) ; $i++) { 
            $expenses_reason[] = ExpenseItem::query()->where("expense_id","=",$expenses_items[$i]['id'])->get()[0];
        }
        $total_ = 0 ;
        for ($i=0; $i < count($expenses_reason) ; $i++) { 
            $total_+=$expenses_reason[$i]['total'];
        }
        $records_comprobante_electronico = $this->recordsDocumentMesAnterior($request); // para los ingresos document
        $records_sales_note = $this->recordsSaleNoteMesAnterior($request) ; // para sales note
        $ventas_totales = $this->calculatePorcentageTotalIngresos($records_sales_note,$records_comprobante_electronico);
        $promedio = $ventas_totales["total_bruto"] == 0 ?0: (($total_ * 100)/$ventas_totales["total_bruto"]) /100 ;
        $total = [$promedio];

        return [
            "records_porcentaje_6_meses"=>  [
                        'totals' => [
                            'total_documents' => number_format(1,2, ".", ""),
                            'total' => number_format( 1 ,2, ".", ""),
                        ],
                        'graph' => [
                            'labels' => array_values($expense_reason),
                            'datasets' => [
                                [
                                    'label' => 'Productos mas Vendidos',
                                    'data' => array_values($total),
                                    'backgroundColor' => 'rgb(43, 116, 180)',
                                    'borderColor' => 'rgb(43, 116, 180)',
                                    'borderWidth' => 1,
                                    'fill' => false,
                                    'lineTension' => 0,
                                ]
                            ],
                        ]
                    ]
        ];
    }
    public function recordsDocumentMesAnterior(){
        $date_start = date("Y-m-d",strtotime(date("Y-m-d") . "-6 month")); 
        $records = Document::query()->whereBetween('created_at',[$date_start . " 00:00:00",date("Y-m-d") . " 23:59:59"])->get();
        return $records ;
    }
    public function recordsSaleNoteMesAnterior()
    {
        $date_start = date("Y-m-d",strtotime(date("Y-m-d") . "-6 month")); 
        $records = SaleNote::query()->whereBetween('created_at',[$date_start . " 00:00:00",date("Y-m-d") . " 23:59:59"])->get();
        return $records;
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
    
}

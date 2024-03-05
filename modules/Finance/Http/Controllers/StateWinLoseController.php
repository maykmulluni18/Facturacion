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
use App\Http\Resources\Tenant\PurchaseCollection;
use App\Models\Tenant\Document;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Models\Tenant\SaleNote;
use App\Models\Tenant\Purchase;
use Modules\Expense\Models\Expense;
use Modules\Finance\Models\Indire;


class StateWinLoseController extends Controller
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

    public function records(Request $request)
    {
        $params = $request->all();
        $date_start = $params['date_start'];
        $date_end = $params['date_end'];
        $records_doc = Document::query()->whereBetween('created_at',[$date_start,$date_end]); 
        $records_doc = new DocumentCollection($records_doc->paginate(config('tenant.items_per_page')));

        $records_sale_note = SaleNote::query()->whereBetween('created_at',[$date_start,$date_end]);
        $records_sale_note = new SaleNoteCollection($records_sale_note->paginate(config('tenant.items_per_page')));
        $records_purchase = $this->getRecordsPurchases($date_start,$date_end);
        $records_expense = Expense::query()->get();
        $total = 0;
        for ($i=0; $i < count($records_doc) ; $i++) { 
            $total=$total + $records_doc[$i]['total'];
        }

        for ($i=0; $i < count($records_sale_note) ; $i++) { 
            $total=$total + $records_sale_note[$i]['total'];
        }
        $total_purchase=0;
        for ($i=0; $i < count($records_purchase) ; $i++) { 
            $total_purchase=$total_purchase + $records_purchase[$i]['total'];   
        }
        $total_expense=0;
        $total_cts_banco=0;
        $total_transp=0;
        $total_others_expense=0;
        for ($i=0; $i < count($records_expense) ; $i++) { 
            $item = $records_expense[$i]['expense_reason_id'];
            if($item == 17) $total_expense = $total_expense + $records_expense[$i]['total'];
            else if($item == 38 || $item == 40) $total_cts_banco = $total_cts_banco + $records_expense[$i]['total'];
            else if($item == 15 || $item == 16 || $item == 21 || $item == 29 ) $total_transp = $total_transp + $records_expense[$i]['total'];
            else if($item == 13 || $item == 13 || $item == 37 || $item == 41 || $item == 42 ) $total_others_expense = $total_others_expense + $records_expense[$i]['total'];
        }
        // $records_indirect_expense = $this->getRecords($request->all(),::class)->all();
        $records_indirect_expense = IndirectExpense::query()->whereBetween("created_at",[$date_start,$date_end])->get();

        return [
            "total" => $total ,"totals_buys"=>$total_purchase,"totals_labor_directa"=>$total_expense,
            "total_transp"=>$total_transp,"total_comision"=>$total_cts_banco,"total_others_expense"=>$total_others_expense,
            "records_expense_indirect"=>$records_indirect_expense
        ];
    }
    public function getRecordsPurchases($date_start,$date_end){
        $records = Purchase::whereBetween('created_at',[$date_start,$date_end])->get() ;
        return $records;
    }
    
    

}

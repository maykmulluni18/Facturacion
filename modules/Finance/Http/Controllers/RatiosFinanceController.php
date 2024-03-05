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
use App\Models\Tenant\Cash;
use Modules\Pos\Models\CashTransaction;
use App\Models\Tenant\Document;
use Modules\Finance\Helpers\ToPay;
use Modules\Finance\Http\Resources\UnpaidCollection;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Models\Tenant\Bank;
use App\Models\Tenant\SaleNote;
use Modules\Finance\Models\GlobalPayment;
use App\Models\Tenant\Configuration;

use App\Models\Tenant\BankAccount;
use Modules\Dashboard\Helpers\DashboardView;



class RatiosFinanceController extends Controller
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
    public function indexRatiosFinance()
    {
        $isMovements = 0;
        return view('finance::ratiosfinance.index', compact('isMovements'));
    }
    // cuentas por cobrar
    public function recordsCuentasPorCobrar(Request $request){
        $records = (new DashboardView())->getUnpaidFilterUser($request->all());
        $config = Configuration::first();
        return (new UnpaidCollection($records->paginate(config('tenant.items_per_page'))))->additional([
            'configuration' => $config->finances
        ]);
    }
    // cuentas por pagar 
    public function cuentasPorPagar(Request $request){
        $data =$request->all();
        if($request->establishment_id === 0){
            $data['withBankLoan'] = 1;
            $data['stablishmentTopaidAll'] = 1; // Lista todos los establecimients
        }

        return [
            'records' => ToPay::getToPay($data)
       ];
    }
    // Suma de saldos de todas las cuentas 
    public function recordssumaSaldoTodasCuentas(Request $request)
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

    public function getInventoryValuedSol(Request $request){
        $records_supplies = Supplie::query()->get();
        $records_subrecipe = RecipesSubrecipe::query()->where('type_doc',"=","recipesub")->get();
        $records_recipe = RecipesSubrecipe::query()->where('type_doc',"=","recipe")->get();
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
    public function inventoryValuedFinal(Request $request){
        $params = $request->all();
        $d_start = $params['date_start'];
        $d_end = $params['date_end'];
        $invetory_total_supplie = $this->getTotalInventorySupplies($d_start . " 00:00:00",$d_end . " 23:59:59"); // [start,end]
        $invetory_total_recipe = $this->getTotalInventorySubRecipeRecipe($d_start . " 00:00:00",$d_end . " 23:59:59"); // [start,end]
        $resp =( ( ($invetory_total_supplie['end'] + $invetory_total_recipe['end']) - ($invetory_total_supplie['start'] + $invetory_total_recipe['start']))/2)/100;
        $alls_sales = $this->recordsDocumentModel($request);
        $alls_sales = $alls_sales + $this->recordsSaleNote($request);
        return  ["data"=>$resp,"totals_sales"=>$alls_sales] ;
    }
    public function getTotalInventorySupplies($date_start,$date_end){
        $costs_start = 0;
        $costs_end = 0;
        $supplies = Supplie::query()->get();
        for ($i=0; $i < count($supplies) ; $i++) { 
            $resp = SuppliesLog::query()->where("supplies_id","=",$supplies[$i]['id'])->whereBetween("created_at",[$date_start,$date_end])->orderBy("created_at","ASC")->get();            
            if(count($resp) != 0){
                $costs_start = $resp[0]['stock_start'] * $supplies[$i]['costs_unit'];
                $costs_end = $resp[count($resp) - 1]['stock_end'] * $supplies[$i]['costs_unit'];
            }
        }
        return ["start"=>$costs_start/1000,"end"=>$costs_end/1000];
    }
    public function getTotalInventorySubRecipeRecipe($date_start,$date_end){
        $costs_start = 0;
        $costs_end = 0;
        $recipes = RecipesSubrecipe::query()->get();
        for ($i=0; $i < count($recipes) ; $i++) { 
            $costs_item = 0;
            $resp = RecipesSubrecipeLog::query()->where("item_id","=",$recipes[$i]['id'])->whereBetween("created_at",[$date_start,$date_end])->orderBy("created_at","ASC")->get();
            $subrecipes_supplies = json_decode($recipes[$i]['subrecipes_supplies'],true);
            for ($j=0; $j < count($subrecipes_supplies) ; $j++) { 
                $costs_item = $costs_item + (float) $subrecipes_supplies[$j]['costs_by_grams'] ;
            }
            if(count($resp) != 0){
                $costs_start = $costs_start + ($resp[0]['stock_start'] * $costs_item);
                $costs_end = $costs_end + ($resp[count($resp) - 1]['stock_end'] * $costs_item);
            }
        }
        return ["start"=>$costs_start/1000,"end"=>$costs_end/1000];
    }
    public function recordsDocumentModel($request){
        $total = $this->getRecordsDocument($request);
        return $total ;
    }
    public function getRecordsDocument($request){
        $total = 0;
        $records = Document::query()->get();
        for ($i=0; $i < count($records) ; $i++) { 
            $total = $total + $records[$i]['total'];
        }
        return $total;
    }

    public function recordsSaleNote(Request $request)
    {
        $total = $this->getRecordsSaleNote($request);   
        return $total;

    }
    private function getRecordsSaleNote($request){
        $total = 0;        
        $records = SaleNote::query()->get();
        for ($i=0; $i < count($records) ; $i++) { 
            $total = $total + $records[$i]['total'];
        }
        return $total;
    }
}

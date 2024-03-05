<?php

namespace Modules\Expense\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Expense\Models\Expense;
use Modules\Expense\Models\ExpenseReason;
use Modules\Expense\Models\ExpensePayment;
use Modules\Expense\Models\ExpenseType;
use Modules\Expense\Models\ExpenseMethodType;
use Modules\Expense\Models\ExpenseItem;
use Modules\Expense\Http\Resources\ExpenseCollection;
use Modules\Expense\Http\Resources\ExpenseResource;
use Modules\Expense\Http\Requests\ExpenseRequest;
use Illuminate\Support\Str;
use App\Models\Tenant\Person;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\Models\Tenant\Establishment;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant\Company;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Expense\Exports\ExpenseExport;
use Carbon\Carbon;
use App\CoreFacturalo\Helpers\Functions\GeneralPdfHelper;
use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use Exception;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Models\Tenant\SaleNote;
use App\Http\Resources\Tenant\DocumentCollection;
use App\Models\Tenant\Document;
use App\Models\Tenant\SmallBox;
use App\Models\Tenant\BankAccount;



class ExpenseController extends Controller
{

    use FinanceTrait, StorageDocument ;

    public function index()
    {
        return view('expense::expenses.index');
    }


    public function create($id = null)
    {
        return view('expense::expenses.form', compact('id'));
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión',
            'number' => 'Número',
        ];
    }

    public function getRecordsFlowFinance($request,$model){
        // $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];

        return $model::with('state_type')->whereBetween('date_of_issue', [$date_start, $date_end])->whereTypeUser()->latest() ;
    }
    public function recordsfromflowfinance(Request $request){
        // dd($request->all());
        $records = $this->getRecordsFlowFinance($request->all(), Expense::class);
        return new ExpenseCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function currentBalance(Request $request){
        $params = $request->all();
        $balance_banks = (float) BankAccount::selectRaw("SUM(initial_balance) as balance")->get()[0]['balance']; // suma de todas las cuentas
        // $sale_note_balance = (float) SaleNote::selectRaw("SUM(sale_note_payments.payment) as amount")
        //                                 ->join("sale_note_payments","sale_notes.id","=","sale_note_payments.sale_note_id")
        //                                 ->whereBetween("created_at",[$params["date_start"] . " 00:00:00",$params['date_end'] . " 23:59:59"])->get()[0]["amount"];

        // $comprobante_electronico = (float) Document::selectRaw("SUM(document_payments.payment) as amount")
        //                                 ->join("document_payments","documents.id","=","document_payments.document_id")
        //                                 ->whereBetween("created_at",[$params["date_start"] . " 00:00:00",$params['date_end'] . " 23:59:59"])->get()[0]["amount"];
        // $total  = $comprobante_electronico + $sale_note_balance ;
        return [
            "saldo_inicial"=>$balance_banks,
        ];
    }
        /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Tenant\SaleNoteCollection
     */
    public function recordsfromflowfinancerigth(Request $request)
    {

        $records = $this->getRecordsfromflowfinancerigth($request);
        
        /* $records = new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        dd($records); */
        return new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));

    }
    public function recordsfromflowfinanceleftinrgresos(Request $request){
        $records = $this->getRecordsleft($request);
        return new DocumentCollection($records->paginate(config('tenant.items_per_page')));
    }
    public function getRecordsleft($request){

        $d_end = $request->d_end;
        $d_start = $request->d_start;
        $date_of_issue = $request->date_of_issue;
        $date_of_due = $request->date_of_due;
        $document_type_id = $request->document_type_id;
        $state_type_id = $request->state_type_id;
        $number = $request->number;
        $series = $request->series;
        $pending_payment = ($request->pending_payment == "true") ? true:false;
        $customer_id = $request->customer_id;
        $item_id = $request->item_id;
        $category_id = $request->category_id;
        $purchase_order = $request->purchase_order;
        $guides = $request->guides;
        $plate_numbers = $request->plate_numbers;

        $records = Document::query();
		if ($d_start && $d_end) {
			 $records->whereBetween('date_of_issue', [$d_start, $d_end]);
		}
        if ($date_of_issue) {
            $records = Document::where('date_of_issue', 'like', '%' . $date_of_issue . '%');
        }
        if ($date_of_due) {
            $records = Document::where('date_of_due', 'like', '%' . $date_of_due . '%');
        }
        /** @var Builder $records */
        if ($document_type_id) {
            $records->where('document_type_id', 'like', '%' . $document_type_id . '%');
        }
        if ($series) {
            $records->where('series', 'like', '%' . $series . '%');
        }
        if ($number) {
            $records->where('number', $number);
        }
        if ($state_type_id) {
            $records->where('state_type_id', 'like', '%' . $state_type_id . '%');
        }
        if ($purchase_order) {
            $records->where('purchase_order', $purchase_order);
        }
        $records->whereTypeUser()->latest();

        if ($pending_payment) {
            $records->where('total_canceled', false);
        }

        if ($customer_id) {
            $records->where('customer_id', $customer_id);
        }

        if ($item_id) {
            $records->whereHas('items', function ($query) use ($item_id) {
                $query->where('item_id', $item_id);
            });
        }

        if ($category_id) {
            $records->whereHas('items', function ($query) use ($category_id) {
                $query->whereHas('relation_item', function ($q) use ($category_id) {
                    $q->where('category_id', $category_id);
                });
            });
        }
        if (!empty($guides)) {
            $records->where('guides', 'like', DB::raw("%\"number\":\"%") . $guides . DB::raw("%\"%"));
        }
        if ($plate_numbers) {
            $records->where('plate_number', 'like', '%' . $plate_numbers . '%');
        }
        return $records;
    }

    /**
     * @param $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getRecordsfromflowfinancerigth($request){
        $records = SaleNote::whereTypeUser();
        // Solo devuelve matriculas
        if($request != null && $request->has('onlySuscription') && (bool)$request->onlySuscription == true){
            $records->whereNotNull('grade')->whereNotNull('section') ;
        }
        // Solo devuelve Suscripciones que tengan relacion en user_rel_suscription_plans.
        if($request != null && $request->has('onlyFullSuscription') && (bool)$request->onlyFullSuscription == true){
            $records->whereNotNull('user_rel_suscription_plan_id')
                ->whereNull('grade')->whereNull('section')
            ;
        }
        if($request->column == 'customer'){
            $records->whereHas('person', function($query) use($request){
                                    $query
                                        ->where('name', 'like', "%{$request->value}%")
                                        ->orWhere('number', 'like', "%{$request->value}%");
                                })
                                ->latest();

        }else{
            $records->where($request->column, 'like', "%{$request->value}%")
                    ->latest('id');
        }
        if($request->series) {
            $records->where('series', 'like', '%' . $request->series . '%');
        }
        if($request->number) {
            $records->where('number', 'like', '%' . $request->number . '%');
        }
        if($request->total_canceled != null) {
            $records->where('total_canceled', $request->total_canceled);
        }

        if($request->purchase_order) {
            $records->where('purchase_order', $request->purchase_order);
        }
        if($request->license_plate) {
            $records->where('license_plate', $request->license_plate);
        }
        return $records;
    }


    public function records(Request $request)
    {
        $records = $this->getRecords($request->all(), Expense::class);
                        
        return new ExpenseCollection($records->paginate(config('tenant.items_per_page')));

        /*$records = Expense::where($request->column, 'like', "%{$request->value}%")
                            ->whereTypeUser()
                            ->latest();

        return new ExpenseCollection($records->paginate(config('tenant.items_per_page')));*/
    }

    public function getRecords($request, $model){

        $period = $request['period'];
        $date_start = $request['date_start'];
        $date_end = $request['date_end'];
        $month_start = $request['month_start'];
        $month_end = $request['month_end'];

        $d_start = null;
        $d_end = null;
    

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($month_start.'-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_start.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($month_start.'-01')->format('Y-m-d');
                $d_end = Carbon::parse($month_end.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'date':
                $d_start = $date_start;
                $d_end = $date_start;
                break;
            case 'between_dates':
                $d_start = $date_start;
                $d_end = $date_end;
                break;
        }

        $records = $this->data($d_start, $d_end, $model);

        return $records;

    }

    private function data($date_start, $date_end, $model)
    {
        $data = $model::with('state_type')->whereBetween('date_of_issue', [$date_start, $date_end])->whereTypeUser()->latest();
        return $data;
    }


    public function tables()
    {
        $suppliers = $this->table('suppliers');
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $currency_types = CurrencyType::whereActive()->get();
        $expense_types = ExpenseType::get();
        $expense_method_types = ExpenseMethodType::all();
        $expense_reasons = ExpenseReason::all();
        $payment_destinations = $this->getBankAccounts();

        return compact('suppliers', 'establishment','currency_types', 'expense_types', 'expense_method_types', 'expense_reasons', 'payment_destinations');
    }



    public function record($id)
    {
        $record = new ExpenseResource(Expense::findOrFail($id));

        return $record;
    }
    // para guardar en caja chica
    public function storeWithData($inputs)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $data = $inputs;
            SmallBox::query()->updateOrCreate(['id' => $inputs['id']], $data);
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'data' =>"ok",
            ];

        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function store(ExpenseRequest $request)
    {
        // expense_reason_id	14
        $params = $request->all();
        if($params['expense_reason_id'] == 14){
            $expense_reason_id = 14 ;
            $total = $params['total'];
            $expense_reas = ExpenseReason::query()->where("id","=",$expense_reason_id)->get();
            $data = ["id"=>null,"description_movement"=>$expense_reas[0]['description'],"type_movement"=>0/*0=Ingreso*/,"date_movement"=>date('Y-m-d h:m:s'),"amount_movement"=>$total]; // "type_movement"=>1 quiere decir que es gasto
            $this->storeWithData($data);
        }
        $data = self::merge_inputs($request);
        // dd($data);

        $expense = DB::connection('tenant')->transaction(function () use ($data) {

            // $doc = Expense::create($data);
            $doc = Expense::updateOrCreate(['id' => $data['id']], $data);

            $doc->items()->delete();

            foreach ($data['items'] as $row)
            {
                $doc->items()->create($row);
            }

            $this->deleteAllPayments($doc->payments);

            foreach ($data['payments'] as $row)
            {
                $record_payment = $doc->payments()->create($row);
                
                if($row['expense_method_type_id'] == 1){
                    $row['payment_destination_id'] = 'cash';
                }

                $this->createGlobalPayment($record_payment, $row);
            }

            $this->setFilename($doc);
            $this->createPdf($doc);

            return $doc;
        });

        return [
            'success' => true,
            'data' => [
                'id' => $expense->id,
            ],
        ];
    }
    

    /**
     * 
     * Imprimir gasto
     *
     * @param  string $external_id
     * @param  string $format
     * @return mixed
     */
    public function toPrint($external_id, $format = 'a4') 
    {
        $record = Expense::where('external_id', $external_id)->first();

        if (!$record) throw new Exception("El código {$external_id} es inválido, no se encontro el registro relacionado");

        // si no tienen nombre de archivo, se regulariza
        if(!$record->filename) $this->setFilename($record);

        $this->createPdf($record, $format, $record->filename);

        return GeneralPdfHelper::getPreviewTempPdf('expense', $this->getStorage($record->filename, 'expense'));
    }

    
    /**
     * 
     * Asignar nombre de archivo
     *
     * @param  Expense $expense
     * @return void
     */
    private function setFilename(Expense $expense)
    {
        $expense->filename = GeneralPdfHelper::getNumberIdFilename($expense->id, $expense->number);
        $expense->save();
    }

        
    /**
     * 
     * Crear pdf para gastos
     *
     * @param  Expense $expense
     * @param  string $format_pdf
     * @return void
     */
    public function createPdf(Expense $expense, $format_pdf = 'a4') 
    {
        $file_content = GeneralPdfHelper::getBasicPdf('expense', $expense, $format_pdf);

        $this->uploadStorage($expense->filename, $file_content, 'expense');
    }


    public static function merge_inputs($inputs)
    {

        $company = Company::active();

        $values = [
            'user_id' => auth()->id(),
            'state_type_id' => $inputs['id'] ? $inputs['state_type_id'] : '05',
            'soap_type_id' => $company->soap_type_id,
            'external_id' => $inputs['id'] ? $inputs['external_id'] : Str::uuid()->toString(),
            'supplier' => PersonInput::set($inputs['supplier_id']),
        ];

        $inputs->merge($values);

        return $inputs->all();
    }

    public function table($table)
    {
        switch ($table) {
            case 'suppliers':

                $suppliers = Person::whereType('suppliers')->orderBy('name')->get()->transform(function($row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number.' - '.$row->name,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $suppliers;

                break;
            default:

                return [];

                break;
        }
    }

    public function voided ($record)
    {
        try {
            $expense = Expense::findOrFail($record);
            $expense->state_type_id = 11;
            $expense->save();
            return [
                'success' => true,
                'data' => [
                    'id' => $expense->id,
                ],
                'message' => 'Gasto anulado exitosamente',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => [
                    'id' => $record,
                ],
                'message' => 'Falló al anular',
            ];
        }
    }

    public function excel(Request $request) {

        /*$records = Expense::where($request->column, 'like', "%{$request->value}%")
                            ->whereTypeUser()
                            ->latest()
                            ->get();*/

        $records = $this->getRecords($request->all(), Expense::class)->get();

        $establishment = auth()->user()->establishment;
        $balance = new ExpenseExport();
        $balance
            ->records($records)
            ->establishment($establishment);

        return $balance->download('Expense_'.Carbon::now().'.xlsx');

    }

}

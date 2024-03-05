<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\Http\Controllers\Controller;

use App\Http\Resources\Tenant\OrdersScheduleCollection;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\OrdersSchedule;
use Modules\Sale\Models\Contract;

use App\Traits\OfflineTrait;

use Illuminate\Http\Request;

use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Traits\InventoryTrait;

// use App\Models\Tenant\Warehouse;

class OrderScheduleController extends Controller
{

    use FinanceTrait;
    use InventoryTrait;
    use SearchTrait;
    use StorageDocument;
    use OfflineTrait;

    protected $order_schedule;
    protected $company;
    protected $apply_change;

    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::select('ticket_58')->first();

        return view('tenant.orderschedule.index', compact('soap_company', 'configuration'));
    }


    public function create($id = null)
    {
        return view('tenant.sale_notes.form', compact('id'));
    }


  
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Tenant\SaleNoteCollection
     */
    public function records(Request $request)
    {

        $records = $this->getRecords($request->query());
        // $records = new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        // dd($records); 
        $response = [];
        for ($i=0; $i < count($records) ; $i++) { 
            $row = $records[$i];
            for ($j=0; $j < count($row['items']) ; $j++) { 
                $row_item = $row['items'][$j];
                $response[] =[
                'id'                            => $row_item['id'],
                'name'                          => "Noc",
                // 'name'                          => $row_item['No se '],
                'sale_price'                    => $row['total'],
                'owner'                         => $row['customer']->name,
                'tematica'                      => $row_item['tematica'],
                'details'                       => $row_item['details'],
                'unit'                          => $row_item['quantity'],
                'external_id'                   => $row['filename'],
                'delivery_date'                 => $row['delivery_hour']
                ];
            }
        }
        return ["data"=>$response];

    }


    /**
     * @param $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getRecords($request){
        
        // $records = OrdersSchedule::whereTypeUser();
        $records = Contract::query();
        if($request['key'] == 'by_day') $records->whereBetween('delivery_hour',[$request['date_day'] ,explode(" ",$request['date_day'])[0] . " 23:59:59"]);
        else if($request['key'] == 'by_week') $records->whereBetween('delivery_hour',[$request['date_start_week'] ,$request['date_end_week'] ]);
        else if($request['key'] == 'by_month') $records->whereBetween('delivery_date',[date("Y-m-01",strtotime($request['date_month_start'] )) , date("Y-m-t",strtotime($request['date_month_start'] ))]);
        // $resp = ;
        // Solo devuelve matriculas
        return $records->orderBy('delivery_hour','asc')->get()->toArray();
    }

}

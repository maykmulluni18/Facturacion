<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Http\Resources\Tenant\SupplieCollection;
use App\Http\Resources\Tenant\SaleNoteResource;
use App\Http\Resources\Tenant\SaleNoteResource2;
use App\Mail\Tenant\SaleNoteEmail;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\Catalogs\UnitType;
use App\Models\Tenant\Catalogs\AffectationIgvType;
use App\Models\Tenant\Catalogs\AttributeType;
use App\Models\Tenant\Catalogs\ChargeDiscountType;
use App\Models\Tenant\Catalogs\CurrencyType;
use App\Models\Tenant\Catalogs\DocumentType;
use App\Models\Tenant\Catalogs\OperationType;
use App\Models\Tenant\Catalogs\PriceType;
use App\Models\Tenant\Catalogs\SystemIscType;
use App\Models\Tenant\Company;
use App\Models\Tenant\Configuration;
use App\Models\Tenant\Dispatch;
use App\Models\Tenant\Establishment;
use App\Models\Tenant\Item;
use App\Models\Tenant\ItemWarehouse;
use App\Models\Tenant\MigrationConfiguration;
use App\Models\Tenant\PaymentMethodType;
use App\Models\Tenant\Person;
use App\Models\Tenant\Supplie;
use App\Models\Tenant\SaleNoteItem;
use App\Models\Tenant\Document;
use App\Models\Tenant\SaleNoteMigration;
use App\Models\Tenant\Series;
use App\Models\Tenant\User;
use App\Traits\OfflineTrait;
use Carbon\Carbon;
use ErrorException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Document\Traits\SearchTrait;
use Modules\Finance\Traits\FinanceTrait;
use Modules\Inventory\Models\Warehouse;
use Modules\Inventory\Traits\InventoryTrait;
use Modules\Item\Models\ItemLot;
use Modules\Item\Models\ItemLotsGroup;
use Modules\Sale\Helpers\SaleNoteHelper;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use App\Exports\SupplieExport;
use App\Exports\Inventrory1Export;
use App\Models\Tenant\RecipesSubrecipe;
use App\Models\Tenant\RecipesSubrecipeLog;
use App\Models\Tenant\SuppliesLog;
use Modules\Item\Models\Category;



// use App\Models\Tenant\Warehouse;

class SuppliesController extends Controller
{

    use FinanceTrait;
    use InventoryTrait;
    use SearchTrait;
    use StorageDocument;
    use OfflineTrait;

    protected $sale_note;
    protected $supplie_ ;
    protected $company;
    protected $apply_change;
    protected $recipes_subrecipes;

    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::select('ticket_58')->first();

        return view('tenant.supplies.index', compact('soap_company', 'configuration'));
    }


    public function create($id = null)
    {
        return view('tenant.sale_notes.form', compact('id'));
    }
    /**
     * Obtiene la url del servidor de destino configurada en la migracion.
     *
     * @return mixed|string|null
     */
    public function getSaleNoteToOtherSiteUrl(){
            $e = MigrationConfiguration::first();
        return $e!== null?$e->url:'';
    }

    public function columns()
    {
        return [
            'name' => 'Nombre',
            'second_name' => 'Nombre Alternativo',
            'costs_unit' => 'Precio Unitario',
            'unit' => 'Unidad',
            'category_supplies' => 'Categoria Insumo',
        ];
    }

    public function columns2()
    {
        return [
            'series' => Series::whereIn('document_type_id', ['80'])->get(),

        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Http\Resources\Tenant\SaleNoteCollection
     */
    public function records(Request $request)
    {
        $records = $this->getRecords($request);
        return [
            "data"=>$records
        ];
        // return new SupplieCollection($records->paginate(config('tenant.items_per_page')));
    }
    private function getRecords($request){
        $records = Supplie::query()->where("unit","!=","DESC");
        if($request->has('column') && $request->value !=null ) $records->where($request->column, 'like', "%{$request->value}%");
        $data = $records->orderBy('name','asc')->get();
        $response = [];
        for ($i=0; $i < count($data) ; $i++) { 
            $cats = Category::query()->where("id",$data[$i]['category_supplies'])->get();
            $data[$i]['category_name'] = $cats[0]['name'];
            $response[] = $data[$i];
        }
        return $response;
    }

    /**
     * @param $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    


    
    public function tables()
    {
        // $unit_types = UnitType::whereActive()->orderByDescription()->get();
        $cats = Category::query()->get();

        // $cotegory_supplies = [['id'=>1,'key'=>"ctg1",'description'=>"Categoria 1"],['id'=>2,'key'=>"ctg2",'description'=>"Categoria 2"],['id'=>3,'key'=>"ctg3",'description'=>"Categoria 3"]];
        return [
            "data"=>$cats
        ];
    }

    public function record($id)
    {
        $record = Supplie::findOrFail($id);
        $record['category_name'] = Category::query()->where("id","=",$record['category_supplies'])->get()[0]['name'];
        return ["data"=>$record];
    }
    
    public function store(Request $request)
    {
        return $this->storeWithData($request->all());
    }


    public function storeWithData($inputs)
    {
        DB::connection('tenant')->beginTransaction();
        try {
            $data = $this->mergeData($inputs);
            $data['costs_unit'] = (float) $data['costs_unit'];
            $data['unit'] = $data['unit'] == 'GRM' ? 'Kilogramo':'Unidad';
            $data['quantity'] = 0 ;
            $this->supplie_ = Supplie::query()->updateOrCreate(['id' => $inputs['id']], $data) ;
            DB::connection('tenant')->commit();
            return [
                'success' => true,
                'data' => [
                    'id' => $this->supplie_->id,
                    'name' => $this->supplie_->name,
                    'message' => "Insumo agregado con exito",
                ],
            ];

        } catch (Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    
    public function storeinventory2(Request $request){
        try {
            $request = $request->all();
            $id_subrecipe = $request['id_subrecipe'];
            $quantity = $request['quantity'];
            // guardamos el registro
            $this->updateStockSupplie($id_subrecipe,$quantity);
            $this->saveRecordMovementRecipeSubRecipe($id_subrecipe,$quantity,"mas",null,null);
            $this->updateStockSubRecipeForAddStock($id_subrecipe,$quantity); 
            return [
                'success' => true,
                'data' => [
                    'message' => "Sub Receta agregado con exito",
                ]
                ];
        } catch (\Exception $e) {  
            throw $e;
        }
    }
    public function saveRecordMovementRecipeSubRecipe($id_subrecipe,$quantity,$movement,$contract_id=null,$item_id=null){
        $supplie = RecipesSubrecipe::where('id',$id_subrecipe)->get() ;
        $params = [
            "id"=>null,
            "amount"=>$movement == "mas"?$quantity:-$quantity,
            "stock_start"=>(float) $supplie[0]['quantity'],
            "stock_end"=> $movement == "mas"? (float) $supplie[0]['quantity'] +(float) $quantity:(float) $supplie[0]['quantity'] - (float) $quantity ,
            "recipe_subrecipe_id"=>$id_subrecipe,
            "item_id"=>$item_id,
            "contract_id"=>$contract_id,
        ];
        RecipesSubrecipeLog::query()->updateOrCreate(['id' =>$params["id"] ], $params);
    }
    public function updateStockSupplie($id_subrecipe,$quantity){
        try {
            $total = 0;
            $sub_recipe = RecipesSubrecipe::where('id',$id_subrecipe)->get();
            $subrecipes_supplies = json_decode($sub_recipe[0]['subrecipes_supplies']);
            for ($i=0; $i < count($subrecipes_supplies); $i++) { 
                $total+=$subrecipes_supplies[$i]->quantity;
            }

            $porcentage_expect = ((100 * $quantity) / $total)/100;// aca te dira el porcentaje que se debe quitar de cada insumo            
            for ($i=0; $i < count($subrecipes_supplies); $i++) {
                $item = $subrecipes_supplies[$i] ;
                $supplie = Supplie::where('id',$item->id_supplie)->get() ;
                $amount_substract = $item->quantity *  $porcentage_expect ;
                $this->saveRecordsMovementSupplie($item->id_supplie,(float) $supplie[0]['quantity'],$amount_substract,"menos",$supplie[0]['costs_unit'],((float)$supplie[0]['quantity'] * (float)$supplie[0]['costs_unit'] ));
                Supplie::where('id',$item->id_supplie)->update(['quantity'=> (float) $supplie[0]['quantity'] - $amount_substract ]) ;
            }
            return "ok";
        } catch (Exception $e) {
            throw $e->getMessage();
        }
    }
    public function updateStockSubRecipeForAddStock($id_subrecipe,$quantity){
        try {
            $supplie = RecipesSubrecipe::where('id',$id_subrecipe)->get() ;
            $new_quantity = (float) $supplie[0]['quantity'] + (float) $quantity ;
            RecipesSubrecipe::where('id',$id_subrecipe)->update(['quantity'=>$new_quantity]) ;
            return "ok";
        } catch (Exception $e) {
            throw $e->getMessage();
        }
    }
   
    public function storeinventory3(Request $request){
        try {
            $request = $request->all();
            $id_recipe = $request['id_recipe'];
            $quantity = $request['quantity'];
            $item_id = $request['item_id'];
            $this->updateStockSubRecipe($id_recipe,$quantity);
            $this->saveRecordMovementRecipeSubRecipe($id_recipe,$quantity,"mas",null,$item_id);
            $this->updateStockSubRecipeForAddStock($id_recipe,$quantity);
            return [
                'success' => true,
                'data' => [
                    'message' => "Receta agregado con exito",
                ]
                ];
        } catch (\Exception $e) {  
            throw $e;
        }
    }
    
    public function updateStockSubRecipe($id_recipe,$quantity){
        $total = 0;
        $recipe = RecipesSubrecipe::where('id',$id_recipe)->get();
        $subrecipes_supplies = json_decode($recipe[0]['subrecipes_supplies']);
        for ($i=0; $i < count($subrecipes_supplies) ; $i++) { 
            $item = $subrecipes_supplies[$i];
            if($item->type == 'supplies') $total+=(float)$item->quantity;
            else $total+=(float)$item->quantity;
        }
        $porcentage_expect = ( (100 * $quantity) / $total ) / 100 ;// aca te dira el porcentaje que se debe quitar de cada insumo
        for ($i=0; $i < count($subrecipes_supplies) ; $i++) { 
            $item = $subrecipes_supplies[$i];
            if($item->type == 'supplies'){
                $supplie = Supplie::where('id',$item->id_supplie)->get() ;
                $amount_substract = $item->quantity *  $porcentage_expect ;
                $this->saveRecordsMovementSupplie($item->id_supplie,(float) $supplie[0]['quantity'],$amount_substract,"menos",$supplie[0]['costs_unit'],((float)$supplie[0]['costs_unit'] * (float) $supplie[0]['quantity']));
                Supplie::where('id',$item->id_supplie)->update(['quantity'=> (float) $supplie[0]['quantity'] - $amount_substract ] )  ;
            }else {
                $sub_recipe = RecipesSubrecipe::where('id',$item->id_subrecipe)->get();
                $amount_substract = $item->quantity *  $porcentage_expect ;
                $this->saveRecordMovementRecipeSubRecipe($item->id_subrecipe,$amount_substract,"menos",null,$sub_recipe[0]['item_id']);
                RecipesSubrecipe::where('id',$item->id_subrecipe)->update(['quantity'=>$sub_recipe[0]['quantity'] - $amount_substract]) ;
            }
        }
        return "ok";
    }
   
    public function updatestocksupplies(Request $request){
        try {
            $inputs = $request->all();
            for ($i=0; $i < count($inputs) ; $i++) { 
                $supplie = Supplie::where('id',$inputs[$i]['id'])->get();
                $this->saveRecordsMovementSupplie($inputs[$i]['id'],(float) $supplie[0]['quantity'],(float) $inputs[$i]['quantity'],"mas",$inputs[$i]['unit_value'],$inputs[$i]['total']);
                $new_quantity = (float) $supplie[0]['quantity'] + (float) $inputs[$i]['quantity'];
                Supplie::where('id',$inputs[$i]['id'])->update(['quantity'=>$new_quantity]);
            }
            return [
                'success' => true,
                'data' => [
                    'message' => "Insumo agregado con exito",
                ]
                ];
        } catch (Exception $e) {
            throw $e;
        }

    }
    public function saveRecordsMovementSupplie($id_supplie,$stock_start,$amount,$movement,$unit_price,$total){ // agregar stock

        $params = [
                "id"=>null,
                "amount"=>$movement == "mas" ?$amount:-$amount,
                "stock_start"=>$stock_start,
                "stock_end"=> $movement == "mas"? (float)$stock_start + $amount:(float)$stock_start - $amount,
                "unit_price"=>(float)$unit_price,
                "total"=>(float)$total,
                "supplies_id"=>$id_supplie,
                "purchases_id"=>0
        ];
        SuppliesLog::query()->updateOrCreate(['id' =>$params["id"] ], $params) ;

    }
    
    public function destroy($id)
    {
        try {

            $item = Supplie::findOrFail($id);
            $item->delete();

            return [
                'success' => true,
                'message' => 'Producto eliminado con éxito'
            ];

        } catch (Exception $e) {
            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'El producto esta siendo usado por otros registros, no puede eliminar'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar el producto'];
        }
    }

    public function mergeData($inputs)
    {
        return $inputs;
    }
    public function export(Request $request)
    {
        $d_start = null;
        $d_end = null;
        $period = $request->period;

        switch ($period) {
            case 'month':
                $d_start = Carbon::parse($request->month_start.'-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_start.'-01')->endOfMonth()->format('Y-m-d');
                break;
            case 'between_months':
                $d_start = Carbon::parse($request->month_start.'-01')->format('Y-m-d');
                $d_end = Carbon::parse($request->month_end.'-01')->endOfMonth()->format('Y-m-d');
                break;
        }

        $items = Supplie::query();
        $extradata = [];
        $isinventory = $request->isinventory;
        if($period !== 'all'){
            $items->whereBetween('supplies.created_at', [$d_start, $d_end]);
        }
        // if(){
            $records =  $items->get();
            for ($i=0; $i < count($records) ; $i++) { 
                $records[$i]['category_name'] = Category::query()->where("id",$records[$i]['category_supplies'])->get()[0]['name'];
            }
        // }
        $response=null;
        if( $isinventory  == 1){
            $response = (new Inventrory1Export())
            ->setExtraData($extradata)
            ->records($records)
            ->download('Reporte_Inventory_Imsumos_'.Carbon::now().'.xlsx');
        } else{
            $response = (new SupplieExport())
            ->setExtraData($extradata)
            ->records($records)
            ->download('Reporte_Insumos_'.Carbon::now().'.xlsx');
        }

        return $response ;

    }

}

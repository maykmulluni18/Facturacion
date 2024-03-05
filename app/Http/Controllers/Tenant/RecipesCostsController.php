<?php

namespace App\Http\Controllers\Tenant;

use App\CoreFacturalo\Helpers\Storage\StorageDocument;
use App\CoreFacturalo\Requests\Inputs\Common\EstablishmentInput;
use App\CoreFacturalo\Requests\Inputs\Common\PersonInput;
use App\CoreFacturalo\Template;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SearchItemController;
use App\Http\Requests\Tenant\SaleNoteRequest;
use App\Http\Resources\Tenant\SaleNoteCollection;
use App\Http\Resources\Tenant\SaleNoteResource;
use App\Http\Resources\Tenant\SaleNoteResource2;
use App\Mail\Tenant\SaleNoteEmail;
use App\Models\Tenant\BankAccount;
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
use App\Models\Tenant\SaleNote;
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
use App\Models\Tenant\RecipesSubrecipe;
use App\Models\Tenant\RecipesSubrecipeLog;
use App\Exports\RecipesCostsExport;
use App\Exports\RecipesCostsExport2;
use PDF;

// use App\Models\Tenant\Warehouse;

class RecipesCostsController extends Controller
{

    use FinanceTrait;
    use InventoryTrait;
    use SearchTrait;
    use StorageDocument;
    use OfflineTrait;

    protected $sale_note;
    protected $recipes_subrecipes;
    protected $recipes_subrecipes_details;
    protected $cifs;
    protected $company;
    protected $apply_change;

    public function index()
    {
        $company = Company::select('soap_type_id')->first();
        $soap_company  = $company->soap_type_id;
        $configuration = Configuration::select('ticket_58')->first();

        return view('tenant.recipescosts.index', compact('soap_company', 'configuration'));
    }


    public function create($id = null)
    {
        return view('tenant.sale_notes.form', compact('id'));
    }


    /**
     * Envia la NV al servidor de destino. Devuelve el mensaje de exito o error del servidor
     *
     * @param $saleNoteId
     * @return array
     */
    

    /**
     * Evalua la forma de enviar la nv al servidor.
     *
     * @param Request $request
     * @return array
     */
    
    /**
     * Obtiene la url del servidor de destino configurada en la migracion.
     *
     * @return mixed|string|null
     */
    public function getSaleNoteToOtherSiteUrl(){
            $e = MigrationConfiguration::first();
        return $e!== null?$e->url:'';
    }

    /**
     * Obtiene la lista de nota de ventas que pueden ser migradas a otro servidor.
     *
     * @param Request $request
     * @return SaleNote[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection|mixed
     */
    public function getSaleNoteToOtherSite(Request $request){


        $saleNoteAlready = SaleNoteMigration::where('success',1)
            ->select('sale_notes_id')
            ->get()
            ->pluck('sale_notes_id');
        $configuration = Configuration::first();
        $saleNote = SaleNote::whereNotIn('id',$saleNoteAlready);
        if($request->has('params')){
            $param = $request->params;
            if(isset($param['client_id'])) {
                $saleNote->where('customer_id', $param['client_id']);
            }
            if(isset($param['date_of_issue'])) {
                $saleNote->where('date_of_issue', $param['date_of_issue']);
            }
        }

        $saleNote = $saleNote->where('state_type_id','!=','11')
            ->get()
            ->transform(function($row)use($configuration){
                /** @var SaleNote $row */
                return $row->getCollectionData($configuration);
            });

        return $saleNote;
    }
    /**
     * Busca el texto $search en la cadena de caracteres $text
     * @param $search
     * @param $text
     * @return bool
     */
    public function searchInString($search, $text){
        return !(strpos($text, $search) === false);
    }

    public function columns()
    {
        return [
            'date_of_issue' => 'Fecha de emisión',
            'customer' => 'Cliente',
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
        
        /* $records = new SaleNoteCollection($records->paginate(config('tenant.items_per_page')));
        dd($records); */
        return ["data"=>$records] ;

    }

    /**
     * @param $request
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getRecords($request){
        $records = RecipesSubrecipe::query();
        if($request->has('column')) $records->where($request->column, 'like', "%{$request->value}%");
        $records = $records->get();
        //$records->join('supplies','supplies.id','=','table_recipes');
        return $records;
    }

    public function recordsSubRecipes(Request $request){
        $records = RecipesSubrecipe::query()->where('type_doc',"=","recipesub")->get();
        return ["data"=>$records];
    }

    public function searchCustomers(Request $request)
    {

        $customers = Person::where('number','like', "%{$request->input}%")
                            ->orWhere('name','like', "%{$request->input}%")
                            ->whereType('customers')->orderBy('name')
                            ->whereIsEnabled()
                            ->get()->transform(function(Person $row) {
                                return [
                                    'id' => $row->id,
                                    'description' => $row->number.' - '.$row->name,
                                    'seller_id' => $row->seller_id,
                                    'seller' => $row->seller,
                                    'name' => $row->name,
                                    'number' => $row->number,
                                    'identity_document_type_id' => $row->identity_document_type_id,
                                    'identity_document_type_code' => $row->identity_document_type->code
                                ];
                            });

        return compact('customers');
    }

    public function tables()
    {
        $user = new User();
        if(\Auth::user()){
            $user = \Auth::user();
        }
        $establishment_id =  $user->establishment_id;
        $userId =  $user->id;
        $customers = $this->table('customers');
        $establishments = Establishment::where('id', auth()->user()->establishment_id)->get();
        $currency_types = CurrencyType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $global_charge_types = ChargeDiscountType::whereIn('id', ['50'])->get();
        $company = Company::active();
        $payment_method_types = PaymentMethodType::all();
        $series = collect(Series::all())->transform(function($row) {
            return [
                'id' => $row->id,
                'contingency' => (bool) $row->contingency,
                'document_type_id' => $row->document_type_id,
                'establishment_id' => $row->establishment_id,
                'number' => $row->number
            ];
        });
        $payment_destinations = $this->getPaymentDestinations();
        $configuration = Configuration::select('destination_sale','ticket_58')->first();
        // $sellers = User::GetSellers(false)->get();
        $sellers = User::getSellersToNvCpe($establishment_id,$userId);


        return compact('customers', 'establishments','currency_types', 'discount_types', 'configuration',
                         'charge_types','company','payment_method_types', 'series', 'payment_destinations','sellers', 'global_charge_types');
    }

    public function changed($id)
    {
        $sale_note = SaleNote::find($id);
        $sale_note->changed = true;
        $sale_note->save();
    }


    public function item_tables()
    {
        // $items = $this->table('items');
        $items = SearchItemController::getItemsToSaleNote();
        $categories = [];
        $affectation_igv_types = AffectationIgvType::whereActive()->get();
        $system_isc_types = SystemIscType::whereActive()->get();
        $price_types = PriceType::whereActive()->get();
        $discount_types = ChargeDiscountType::whereType('discount')->whereLevel('item')->get();
        $charge_types = ChargeDiscountType::whereType('charge')->whereLevel('item')->get();
        $attribute_types = AttributeType::whereActive()->orderByDescription()->get();

        $operation_types = OperationType::whereActive()->get();
        $is_client = $this->getIsClient();

        return compact('items',
        'categories',
        'affectation_igv_types',
        'system_isc_types',
        'price_types',
        'discount_types',
        'charge_types',
        'attribute_types',
        'operation_types',
        'is_client'
        );
    }

    public function record($id)
    {
        $record = RecipesSubrecipe::findOrFail($id);
        // if($record[0]['type_doc'] == 'recipe'){
        //     $item = Item::query()->where('id',$record[0]['item_id'])->get();
        //     if($item[0]['has_igv'] == true) $record[0]['sale_price'] = $item[0]['sale_unit_price'];
        //     else $record[0]['sale_price'] = $item[0]['sale_unit_price'] -  ($item[0]['sale_unit_price'] / 1.18);
        // }
        return ["data"=>$record];
    }

    public function record2($id)
    {
        $record = new SaleNoteResource2(SaleNote::findOrFail($id));

        return $record;
    }

    public function store(Request $request)
    {
        return $this->storeWithData($request->all());
    }


    public function storeWithData($inputs)
    {
        DB::connection('tenant')->beginTransaction();
        $recipe_subrecipe = $inputs['recipe_subrecipe']; // array
        $cif = $inputs['cif']; // array
        $type_doc = $inputs['type_doc'];
        $recipes_details = $inputs['recipes_details'];
        $costs = json_encode($inputs['costs']);
        try {
            
            // save recipes or sub recipes
            $this->saveRecipesSubrecipes($recipe_subrecipe,$cif,$type_doc,$recipes_details,$costs); 
            // update in alls sites

            return [
                'success' => true,
                'data' => [
                    'id' => "",
                    'message'=>'Registro Agregar con exito!'
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
    
    public function saveRecipesSubrecipes($recipe_subrecipe,$cif,$type_doc,$recipes_details,$costs){
        try {
            $quantity =0 ;
            if($recipe_subrecipe['id'] != null){
                $item = RecipesSubrecipe::query()->where("id","=",$recipe_subrecipe['id'])->get();
                $quantity =(float) $item[0]["quantity"];
            }
            $data = [
                'name'=> $recipe_subrecipe['id'] != null ? $recipe_subrecipe['name']['description']  : ($type_doc == 'recipe' ?$recipe_subrecipe['name']['description']['description']:$recipe_subrecipe['name']['description']),
                'sale_price'=>$recipe_subrecipe['sale_price'],
                'type_doc'=>$type_doc,
                'quantity'=>$quantity,
                'subrecipes_supplies'=>json_encode($recipes_details),
                'cif'=>json_encode($cif),
                'costs'=>$costs,
                'item_id'=> $recipe_subrecipe['id'] != null ? $recipe_subrecipe['name']['id']: ($type_doc == 'recipe' ? $recipe_subrecipe['name']['description']['id'] : null) ,
                'created_at'=>date("Y-m-d H:i:s"),
                'updated_at'=>date("Y-m-d H:i:s"),
            ];            
            $this->recipes_subrecipes = RecipesSubrecipe::query()->updateOrCreate(['id' => $recipe_subrecipe['id']], $data);
            DB::connection('tenant')->commit();
            if($type_doc == "recipesub"){
                $items = RecipesSubrecipe::query()->where("type_doc","=","recipe")->get();
                for ($j=0; $j < count($items) ; $j++) { 
                    $exist = false;
                    $sub_recipe_ = json_decode($items[$j]['subrecipes_supplies'],true);
                    for ($k=0; $k < count($sub_recipe_) ; $k++) { 
                        $array = $sub_recipe_[$k];
                        if(array_key_exists("id_subrecipe",$array) && $array['id_subrecipe'] == $recipe_subrecipe['id']){
                            $exist = true;
                            $array['name'] = $recipe_subrecipe['name']['description'];
                        }
                        $sub_recipe_[$k] = $array;
                    }
                    if($exist){
                        RecipesSubrecipe::where("id","=",$items[$j]['id'])->update(['subrecipes_supplies'=>json_encode($sub_recipe_)]);
                        DB::connection('tenant')->commit();
                    }
                }
            }
        } catch (\Exception $e) {
            throw $e->getMessage();
        }
    }
    

    /**
     *
     * Obtener lote seleccionado
     *
     * @todo regularizar lots_group, no se debe guardar en bd, ya que tiene todos los lotes y no los seleccionados, reemplazar por IdLoteSelected
     *
     * @param  array $row
     * @return array
     */
    private function getIdLoteSelectedItem($row)
    {
        $id_lote_selected = null;

        if(isset($row['IdLoteSelected']))
        {
            $id_lote_selected = $row['IdLoteSelected'];
        }
        else
        {
            if(isset($row['item']['lots_group']))
            {
                $id_lote_selected = collect($row['item']['lots_group'])->where('compromise_quantity', '>', 0)->toArray();
            }
        }

        return $id_lote_selected;
    }


    /**
     *
     * Asignar lote a item (regularizar propiedad en json item)
     *
     * @param  array $row
     * @return void
     */
    private function setIdLoteSelectedToItem(&$row)
    {
        if(isset($row['IdLoteSelected']))
        {
            $row['item']['IdLoteSelected'] = $row['IdLoteSelected'];
        }
        else
        {
            $row['item']['IdLoteSelected'] = isset($row['item']['IdLoteSelected']) ? $row['item']['IdLoteSelected'] : null;
        }
    }


    private function regularizePayments($payments){

        $total_payments = collect($payments)->sum('payment');

        $balance = $this->sale_note->total - $total_payments;

        if($balance <= 0){

            $this->sale_note->total_canceled = true;
            $this->sale_note->save();

        }else{

            $this->sale_note->total_canceled = false;
            $this->sale_note->save();
        }

    }
    public function destroy($id)
    {
        try {
            $can_delete = true;
            $item = RecipesSubrecipe::findOrFail($id);
            if($item['type_doc'] == 'recipesub') $can_delete = $this->isUsedInOtherSites($id);
            if($can_delete == false) throw new Exception("ITEM_USAGE_OTHER_SITES");
            $item->delete();

            return [
                'success' => true,
                'message' => 'Registro eliminado con éxito'
            ];

        } catch (Exception $e) {
            if($e->getMessage() == "ITEM_USAGE_OTHER_SITES") return ['success' => false,'message' => 'El registro esta Siendo usado por otro'];
            return ($e->getCode() == '23000') ? ['success' => false,'message' => 'El registro esta Siendo usado por otro'] : ['success' => false,'message' => 'Error inesperado, no se pudo eliminar el producto'];

        }
    }
    public function isUsedInOtherSites($item_id){
        $can_delete = true;
        $items = RecipesSubrecipe::query()->where("type_doc","=","recipe")->get();
        for ($i=0; $i < count($items) ; $i++) { 
            $sub_recipe__ = json_decode($items[$i]['subrecipes_supplies'],true);
            for ($j=0; $j < count($sub_recipe__) ; $j++) { 
                $array = $sub_recipe__[$j];
                
                if(array_key_exists("id_subrecipe",$array) && $array['id_subrecipe'] == $item_id) $can_delete = false;
            }
        }
        return $can_delete;
    }

    public function destroy_sale_note_item($id)
    {
        $item = SaleNoteItem::findOrFail($id);

        if(isset($item->item->lots)){

            foreach($item->item->lots as $lot) {
                // dd($lot->id);
                $record_lot = ItemLot::findOrFail($lot->id);
                $record_lot->has_sale = false;
                $record_lot->update();
            }

        }

        $item->delete();

        return [
            'success' => true,
            'message' => 'eliminado'
        ];
    }

    public function mergeData($inputs)
    {

        $this->company = Company::active();

        // Para matricula, se busca el hijo en atributos
        $attributes = $inputs['attributes']??[];
        $children = $attributes['children_customer_id']??null;
        $type_period = isset($inputs['type_period']) ? $inputs['type_period'] : null;
        $quantity_period = isset($inputs['quantity_period']) ? $inputs['quantity_period'] : null;
        $d_of_issue = new Carbon($inputs['date_of_issue']);
        $automatic_date_of_issue = null;

        if($type_period && $quantity_period > 0){

            $add_period_date = ($type_period == 'month') ? $d_of_issue->addMonths($quantity_period): $d_of_issue->addYears($quantity_period);
            $automatic_date_of_issue = $add_period_date->format('Y-m-d');

        }

        if (key_exists('series_id', $inputs)) {
            $series = Series::query()->find($inputs['series_id'])->number;
        } else {
            $series = $inputs['series'];
        }

        $number = null;

        if($inputs['id'])
        {
            $number = $inputs['number'];
        }
        else{

            $document = SaleNote::query()
                                ->select('number')->where('soap_type_id', $this->company->soap_type_id)
                                ->where('series', $series)
                                ->orderBy('number', 'desc')
                                ->first();

            $number = ($document) ? $document->number + 1 : 1;

        }
        $seller_id = isset($inputs['seller_id'])?(int)$inputs['seller_id']:0;
        if($seller_id == 0){
            $seller_id = auth()->id();
        }
        $additional_information = isset($inputs['additional_information'])?$inputs['additional_information']:'';


        $values = [
            'additional_information' => $additional_information,
            'automatic_date_of_issue' => $automatic_date_of_issue,
            'user_id' => auth()->id(),
            'seller_id' => $seller_id,
            'external_id' => Str::uuid()->toString(),
            'customer' => PersonInput::set($inputs['customer_id']),
            'establishment' => EstablishmentInput::set($inputs['establishment_id']),
            'soap_type_id' => $this->company->soap_type_id,
            'state_type_id' => '01',
            'series' => $series,
            'number' => $number
        ];
        if(!empty($children)){
            $customer = PersonInput::set($inputs['customer_id']);
            $customer['children'] = PersonInput::set($children);
            $values['customer'] = $customer;
        }

        $this->setDataPointSystemToValues($values, $inputs);


        unset($inputs['series_id']);

//        $inputs->merge($values);
        $inputs = array_merge($inputs, $values);
        return $inputs;
    }

    
    /**
     * Configuración de sistema por puntos
     *
     * @param  array $values
     * @param  array $inputs
     * @return void
     */
    private function setDataPointSystemToValues(&$values, $inputs)
    {
        $configuration = Configuration::getDataPointSystem();

        $created_from_pos = $inputs['created_from_pos'] ?? false;

        if($created_from_pos && $configuration->enabled_point_system)
        {
            $values['point_system'] = $configuration->enabled_point_system;
            $values['point_system_data'] = [
                'point_system_sale_amount' => $configuration->point_system_sale_amount,
                'quantity_of_points' => $configuration->quantity_of_points,
                'round_points_of_sale' => $configuration->round_points_of_sale,
            ];
        }
    }


//    public function recreatePdf($sale_note_id)
//    {
//        $this->sale_note = SaleNote::find($sale_note_id);
//        $this->createPdf();
//    }

    private function setFilename()
    {
        $name = [$this->sale_note->series,$this->sale_note->number,date('Ymd')];
        $this->sale_note->filename = join('-', $name);

        $this->sale_note->unique_filename = $this->sale_note->filename; //campo único para evitar duplicados

        $this->sale_note->save();
    }

    public function toPrint($external_id, $format) {

        $sale_note = SaleNote::where('external_id', $external_id)->first();

        if (!$sale_note) throw new Exception("El código {$external_id} es inválido, no se encontro la nota de venta relacionada");

        $this->reloadPDF($sale_note, $format, $sale_note->filename);
        $temp = tempnam(sys_get_temp_dir(), 'sale_note');

        file_put_contents($temp, $this->getStorage($sale_note->filename, 'sale_note'));

        return response()->file($temp);
    }

    private function reloadPDF($sale_note, $format, $filename) {
        $this->createPdf($sale_note, $format, $filename);
    }

    public function createPdf($sale_note = null, $format_pdf = null, $filename = null) {

        ini_set("pcre.backtrack_limit", "5000000");
        $template = new Template();
        $pdf = new Mpdf();

        $this->company = ($this->company != null) ? $this->company : Company::active();
        $this->document = ($sale_note != null) ? $sale_note : $this->sale_note;

        $this->configuration = Configuration::first();
        // $configuration = $this->configuration->formats;
        $base_template = Establishment::find($this->document->establishment_id)->template_pdf;

        $html = $template->pdf($base_template, "sale_note", $this->company, $this->document, $format_pdf);

        if (($format_pdf === 'ticket') OR ($format_pdf === 'ticket_58')) {

            $width = ($format_pdf === 'ticket_58') ? 56 : 78 ;
            if(config('tenant.enabled_template_ticket_80')) $width = 76;

            $company_logo      = ($this->company->logo) ? 40 : 0;
            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $payments     = $this->document->payments()->count() * 2;
            $discount_global = 0;
            $extra_by_item_description = 0;
            foreach ($this->document->items as $it) {
                if(strlen($it->item->description)>100){
                    $extra_by_item_description +=24;
                }
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends = $this->document->legends != '' ? '10' : '0';
            $bank_accounts = BankAccount::count() * 6;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    $width,
                    120 +
                    ($quantity_rows * 8)+
                    ($discount_global * 3) +
                    $company_logo +
                    $payments +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $bank_accounts +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $extra_by_item_description +
                    $total_taxed],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);
        } else if($format_pdf === 'a5'){

            $company_name      = (strlen($this->company->name) / 20) * 10;
            $company_address   = (strlen($this->document->establishment->address) / 30) * 10;
            $company_number    = $this->document->establishment->telephone != '' ? '10' : '0';
            $customer_name     = strlen($this->document->customer->name) > '25' ? '10' : '0';
            $customer_address  = (strlen($this->document->customer->address) / 200) * 10;
            $p_order           = $this->document->purchase_order != '' ? '10' : '0';

            $total_exportation = $this->document->total_exportation != '' ? '10' : '0';
            $total_free        = $this->document->total_free != '' ? '10' : '0';
            $total_unaffected  = $this->document->total_unaffected != '' ? '10' : '0';
            $total_exonerated  = $this->document->total_exonerated != '' ? '10' : '0';
            $total_taxed       = $this->document->total_taxed != '' ? '10' : '0';
            $quantity_rows     = count($this->document->items);
            $discount_global = 0;
            foreach ($this->document->items as $it) {
                if ($it->discounts) {
                    $discount_global = $discount_global + 1;
                }
            }
            $legends           = $this->document->legends != '' ? '10' : '0';


            $alto = ($quantity_rows * 8) +
                    ($discount_global * 3) +
                    $company_name +
                    $company_address +
                    $company_number +
                    $customer_name +
                    $customer_address +
                    $p_order +
                    $legends +
                    $total_exportation +
                    $total_free +
                    $total_unaffected +
                    $total_exonerated +
                    $total_taxed;
            $diferencia = 148 - (float)$alto;

            $pdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => [
                    210,
                    $diferencia + $alto
                    ],
                'margin_top' => 2,
                'margin_right' => 5,
                'margin_bottom' => 0,
                'margin_left' => 5
            ]);


       } else {

            $pdf_font_regular = config('tenant.pdf_name_regular');
            $pdf_font_bold = config('tenant.pdf_name_bold');

            if ($pdf_font_regular != false) {
                $defaultConfig = (new ConfigVariables())->getDefaults();
                $fontDirs = $defaultConfig['fontDir'];

                $defaultFontConfig = (new FontVariables())->getDefaults();
                $fontData = $defaultFontConfig['fontdata'];

                $pdf = new Mpdf([
                    'fontDir' => array_merge($fontDirs, [
                        app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                                DIRECTORY_SEPARATOR.'pdf'.
                                                DIRECTORY_SEPARATOR.$base_template.
                                                DIRECTORY_SEPARATOR.'font')
                    ]),
                    'fontdata' => $fontData + [
                        'custom_bold' => [
                            'R' => $pdf_font_bold.'.ttf',
                        ],
                        'custom_regular' => [
                            'R' => $pdf_font_regular.'.ttf',
                        ],
                    ]
                ]);
            }

        }

        $path_css = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.
                                             DIRECTORY_SEPARATOR.'pdf'.
                                             DIRECTORY_SEPARATOR.$base_template.
                                             DIRECTORY_SEPARATOR.'style.css');

        $stylesheet = file_get_contents($path_css);

        $pdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
        $pdf->WriteHTML($html, HTMLParserMode::HTML_BODY);

        if(config('tenant.pdf_template_footer')) {
            /* if (($format_pdf != 'ticket') AND ($format_pdf != 'ticket_58') AND ($format_pdf != 'ticket_50')) */
                if ($base_template != 'full_height') {
                    $html_footer = $template->pdfFooter($base_template,$this->document);
                } else {
                    $html_footer = $template->pdfFooter('default',$this->document);
                }
                $html_footer_legend = "";
                if ($base_template != 'legend_amazonia') {
                    if($this->configuration->legend_footer){
                        $html_footer_legend = $template->pdfFooterLegend($base_template, $this->document);
                    }
                }

                if (($format_pdf === 'ticket') || ($format_pdf === 'ticket_58') || ($format_pdf === 'ticket_50')) {
                    $pdf->WriteHTML($html_footer.$html_footer_legend, HTMLParserMode::HTML_BODY);
                }else{
                    $pdf->SetHTMLFooter($html_footer.$html_footer_legend);
                }
        }

        if ($base_template === 'brand') {

            if (($format_pdf === 'ticket') || ($format_pdf === 'ticket_58') || ($format_pdf === 'ticket_50')) {
                $pdf->SetHTMLHeader("");
                $pdf->SetHTMLFooter("");
            }
        }

        $this->uploadFile($this->document->filename, $pdf->output('', 'S'), 'sale_note');
    }

    public function uploadFile($filename, $file_content, $file_type)
    {
        $this->uploadStorage($filename, $file_content, $file_type);
    }



    public function table($table)
    {
        switch ($table) {
            case 'customers':

                $customers = Person::whereType('customers')
                    ->whereIsEnabled()->orderBy('name')->take(20)->get()->transform(function(Person$row) {
                    return [
                        'id' => $row->id,
                        'description' => $row->number.' - '.$row->name,
                        'seller' => $row->seller,
                        'seller_id' => $row->seller_id,
                        'name' => $row->name,
                        'number' => $row->number,
                        'identity_document_type_id' => $row->identity_document_type_id,
                        'identity_document_type_code' => $row->identity_document_type->code
                    ];
                });
                return $customers;

                break;

            case 'items':

                return SearchItemController::getItemsToSaleNote();
                $establishment_id = auth()->user()->establishment_id;
                $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
                // $warehouse_id = ($warehouse) ? $warehouse->id:null;

                $items_u = Item::whereWarehouse()->whereIsActive()->whereNotIsSet()->orderBy('description')->take(20)->get();

                $items_s = Item::where('unit_type_id','ZZ')->whereIsActive()->orderBy('description')->take(10)->get();

                $items = $items_u->merge($items_s);

                return collect($items)->transform(function($row) use($warehouse){

                    /** @var Item $row */
                    return $row->getDataToItemModal($warehouse);
                    /* Movido al modelo */
                    $detail = $this->getFullDescription($row, $warehouse);
                    return [
                        'id' => $row->id,
                        'full_description' => $detail['full_description'],
                        'brand' => $detail['brand'],
                        'category' => $detail['category'],
                        'stock' => $detail['stock'],
                        'description' => $row->description,
                        'currency_type_id' => $row->currency_type_id,
                        'currency_type_symbol' => $row->currency_type->symbol,
                        'sale_unit_price' => round($row->sale_unit_price, 2),
                        'purchase_unit_price' => $row->purchase_unit_price,
                        'unit_type_id' => $row->unit_type_id,
                        'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                        'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                        'has_igv' => (bool) $row->has_igv,
                        'lots_enabled' => (bool) $row->lots_enabled,
                        'series_enabled' => (bool) $row->series_enabled,
                        'is_set' => (bool) $row->is_set,
                        'warehouses' => collect($row->warehouses)->transform(function($row) use($warehouse_id){
                            return [
                                'warehouse_id' => $row->warehouse->id,
                                'warehouse_description' => $row->warehouse->description,
                                'stock' => $row->stock,
                                'checked' => ($row->warehouse_id == $warehouse_id) ? true : false,
                            ];
                        }),
                        'item_unit_types' => $row->item_unit_types,
                        'lots' => [],
                        // 'lots' => $row->item_lots->where('has_sale', false)->where('warehouse_id', $warehouse_id)->transform(function($row) {
                        //     return [
                        //         'id' => $row->id,
                        //         'series' => $row->series,
                        //         'date' => $row->date,
                        //         'item_id' => $row->item_id,
                        //         'warehouse_id' => $row->warehouse_id,
                        //         'has_sale' => (bool)$row->has_sale,
                        //         'lot_code' => ($row->item_loteable_type) ? (isset($row->item_loteable->lot_code) ? $row->item_loteable->lot_code:null):null
                        //     ];
                        // }),
                        'lots_group' => collect($row->lots_group)->transform(function($row){
                            return [
                                'id'  => $row->id,
                                'code' => $row->code,
                                'quantity' => $row->quantity,
                                'date_of_due' => $row->date_of_due,
                                'checked'  => false
                            ];
                        }),
                        'lot_code' => $row->lot_code,
                        'date_of_due' => $row->date_of_due
                    ];
                });


                break;
            default:

                return [];

                break;
        }
    }


    public function searchItems(Request $request)
    {

        $params = $request->all();
        $value = $params['value'];
        $by = $params['by'];
        $items = Item::query()->where($by, 'like', "%$value%")->get() ;
        /*
        $items = SearchItemController::getItemsToSaleNote($request)->transform(function ($row) use ($warehouse_id, $warehouse) {
            $detail = $this->getFullDescription($row, $warehouse);

            return [
                'id' => $row->id,
                'full_description' => $detail['full_description'],
                'brand' => $detail['brand'],
                'category' => $detail['category'],
                'stock' => $detail['stock'],
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => round($row->sale_unit_price, 2),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_igv' => (bool)$row->has_igv,
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
                'is_set' => (bool)$row->is_set,
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse_id) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                        'checked' => ($row->warehouse_id == $warehouse_id) ? true : false,
                    ];
                }),
                'item_unit_types' => $row->item_unit_types,
                'lots' => [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false
                    ];
                }),
                'lot_code' => $row->lot_code,
                'date_of_due' => $row->date_of_due
            ];
        });
*/
        return compact('items');

    }


    public function searchItemById($id)
    {
        return  SearchItemController::getItemsToSaleNote(null, $id);
        $establishment_id = auth()->user()->establishment_id;
        $warehouse = Warehouse::where('establishment_id', $establishment_id)->first();
        $search_item = $this->getItemsNotServicesById($id);

        if(count($search_item) == 0){
            $search_item = $this->getItemsServicesById($id);
        }

        $items = collect($search_item)->transform(function($row) use($warehouse){
            $detail = $this->getFullDescription($row, $warehouse);
            return [
                'id' => $row->id,
                'full_description' => $detail['full_description'],
                'brand' => $detail['brand'],
                'category' => $detail['category'],
                'stock' => $detail['stock'],
                'description' => $row->description,
                'currency_type_id' => $row->currency_type_id,
                'currency_type_symbol' => $row->currency_type->symbol,
                'sale_unit_price' => round($row->sale_unit_price, 2),
                'purchase_unit_price' => $row->purchase_unit_price,
                'unit_type_id' => $row->unit_type_id,
                'sale_affectation_igv_type_id' => $row->sale_affectation_igv_type_id,
                'purchase_affectation_igv_type_id' => $row->purchase_affectation_igv_type_id,
                'has_igv' => (bool)$row->has_igv,
                'lots_enabled' => (bool)$row->lots_enabled,
                'series_enabled' => (bool)$row->series_enabled,
                'is_set' => (bool)$row->is_set,
                'warehouses' => collect($row->warehouses)->transform(function ($row) use ($warehouse) {
                    return [
                        'warehouse_id' => $row->warehouse->id,
                        'warehouse_description' => $row->warehouse->description,
                        'stock' => $row->stock,
                        'checked' => ($row->warehouse_id == $warehouse->id) ? true : false,
                    ];
                }),
                'item_unit_types' => $row->item_unit_types,
                'lots' => [],
                'lots_group' => collect($row->lots_group)->transform(function ($row) {
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'quantity' => $row->quantity,
                        'date_of_due' => $row->date_of_due,
                        'checked' => false
                    ];
                }),
                'lot_code' => $row->lot_code,
                'date_of_due' => $row->date_of_due
            ];
        });

        return compact('items');
    }


    public function getFullDescription($row, $warehouse){

        $desc = ($row->internal_id)?$row->internal_id.' - '.$row->description : $row->description;
        $category = ($row->category) ? "{$row->category->name}" : "";
        $brand = ($row->brand) ? "{$row->brand->name}" : "";

        if($row->unit_type_id != 'ZZ')
        {
            $warehouse_stock = ($row->warehouses && $warehouse) ? number_format($row->warehouses->where('warehouse_id', $warehouse->id)->first() != null ? $row->warehouses->where('warehouse_id', $warehouse->id)->first()->stock : 0 ,2) : 0;
            $stock = ($row->warehouses && $warehouse) ? "{$warehouse_stock}" : "";
        }
        else{
            $stock = '';
        }


        $desc = "{$desc} - {$brand}";

        return [
            'full_description' => $desc,
            'brand' => $brand,
            'category' => $category,
            'stock' => $stock,
        ];
    }


    public function searchCustomerById($id)
    {
        return $this->searchClientById($id);

    }

    public function option_tables()
    {
        $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
        $series = Series::where('establishment_id',$establishment->id)->get();
        $document_types_invoice = DocumentType::whereIn('id', ['01', '03'])->get();
        $payment_method_types = PaymentMethodType::all();
        $payment_destinations = $this->getPaymentDestinations();
        $sellers = User::GetSellers(false)->get();

        return compact('series', 'document_types_invoice', 'payment_method_types', 'payment_destinations','sellers');
    }

    public function email(Request $request)
    {
        $company = Company::active();
        $record = SaleNote::find($request->input('id'));
        $customer_email = $request->input('customer_email');

        $email = $customer_email;
        $mailable = new SaleNoteEmail($company, $record);
        $id = (int) $request->id;
        $sendIt = EmailController::SendMail($email, $mailable, $id, 2);
        /*
        Configuration::setConfigSmtpMail();
        $array_email = explode(',', $customer_email);
        if (count($array_email) > 1) {
            foreach ($array_email as $email_to) {
                $email_to = trim($email_to);
                if(!empty($email_to)) {
                    Mail::to($email_to)->send(new SaleNoteEmail($company, $record));
                }
            }
        } else {
            Mail::to($customer_email)->send(new SaleNoteEmail($company, $record));
        }*/

        return [
            'success' => true
        ];
    }


    public function dispatches()
    {
        $dispatches = Dispatch::latest()->get(['id','series','number'])->transform(function($row) {
            return [
                'id' => $row->id,
                'series' => $row->series,
                'number' => $row->number,
                'number_full' => "{$row->series}-{$row->number}",
            ];
        }); ;

        return $dispatches;
    }

    public function enabledConcurrency(Request $request)
    {

        $sale_note = SaleNote::findOrFail($request->id);
        $sale_note->enabled_concurrency = $request->enabled_concurrency;
        $sale_note->update();

        return [
            'success' => true,
            'message' => ($sale_note->enabled_concurrency) ? 'Recurrencia activada':'Recurrencia desactivada'
        ];

    }

    public function anulate($id)
    {

        DB::connection('tenant')->transaction(function () use ($id) {

            $obj =  SaleNote::find($id);
            $obj->state_type_id = 11;
            $obj->save();

            // $establishment = Establishment::where('id', auth()->user()->establishment_id)->first();
            $warehouse = Warehouse::where('establishment_id',$obj->establishment_id)->first();

            foreach ($obj->items as $sale_note_item) {

                // voided sets
                $this->voidedSaleNoteItem($sale_note_item, $warehouse);
                // voided sets

                //habilito las series
                // ItemLot::where('item_id', $item->item_id )->where('warehouse_id', $warehouse->id)->update(['has_sale' => false]);
                $this->voidedLots($sale_note_item);

            }

        });

        return [
            'success' => true,
            'message' => 'N. Venta anulada con éxito'
        ];


    }

    public function voidedSaleNoteItem($sale_note_item, $warehouse)
    {

        $warehouse_id = ($sale_note_item->warehouse_id) ? $sale_note_item->warehouse_id : $warehouse->id;

        if(!$sale_note_item->item->is_set){

            $presentationQuantity = (!empty($sale_note_item->item->presentation)) ? $sale_note_item->item->presentation->quantity_unit : 1;

            $sale_note_item->sale_note->inventory_kardex()->create([
                'date_of_issue' => date('Y-m-d'),
                'item_id' => $sale_note_item->item_id,
                'warehouse_id' => $warehouse_id,
                'quantity' => $sale_note_item->quantity * $presentationQuantity,
            ]);

            $wr = ItemWarehouse::where([['item_id', $sale_note_item->item_id],['warehouse_id', $warehouse_id]])->first();

            if($wr)
            {
                $wr->stock =  $wr->stock + ($sale_note_item->quantity * $presentationQuantity);
                $wr->save();
            }

        }else{

            $item = Item::findOrFail($sale_note_item->item_id);

            foreach ($item->sets as $it) {

                $ind_item  = $it->individual_item;
                $item_set_quantity  = ($it->quantity) ? $it->quantity : 1;
                $presentationQuantity = 1;
                $warehouse = $this->findWarehouse($sale_note_item->sale_note->establishment_id);
                $this->createInventoryKardexSaleNote($sale_note_item->sale_note, $ind_item->id , (1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id, $sale_note_item->id);
                if(!$sale_note_item->sale_note->order_note_id) $this->updateStock($ind_item->id , (1 * ($sale_note_item->quantity * $presentationQuantity * $item_set_quantity)), $warehouse->id);

            }

        }

    }


    public function totals(Request $request)
    {

        $records =  $this->getRecords($request)->get(); //SaleNote::where([['state_type_id', '01'],['currency_type_id', 'PEN']])->get();
        $total_pen = 0;
        $total_paid_pen = 0;
        $total_pending_paid_pen = 0;


        $total_pen = $records->sum('total');

        foreach ($records as $sale_note) {

            $total_paid_pen += $sale_note->payments->sum('payment');

        }

        $total_pending_paid_pen = $total_pen - $total_paid_pen;

        return [
            'total_pen' => number_format($total_pen, 2, ".", ""),
            'total_paid_pen' => number_format($total_paid_pen, 2, ".", ""),
            'total_pending_paid_pen' => number_format($total_pending_paid_pen, 2, ".", "")
        ];

    }

    public function downloadExternal($external_id, $format = 'a4')
    {
        $document = SaleNote::where('external_id', $external_id)->first();
        $this->reloadPDF($document, $format, null);
        return $this->downloadStorage($document->filename, 'sale_note');

    }

    
    

    private function voidedLots($item){

        $i_lots_group = isset($item->item->lots_group) ? $item->item->lots_group:[];
        $lot_group_selecteds_filter = collect($i_lots_group)->where('compromise_quantity', '>', 0);
        $lot_group_selecteds =  $lot_group_selecteds_filter->all();

        if(count($lot_group_selecteds) > 0){

            foreach ($lot_group_selecteds as $lt) {
                $lot = ItemLotsGroup::find($lt->id);
                $lot->quantity = $lot->quantity + $lt->compromise_quantity;
                $lot->save();
            }

        }

        if(isset($item->item->lots)){
            foreach ($item->item->lots as $it) {
                if($it->has_sale == true){
                    $ilt = ItemLot::find($it->id);
                    $ilt->has_sale = false;
                    $ilt->save();
                }
            }
        }
    }

    public function saleNotesByClient(Request $request)
    {
        $request->validate([
            'client_id' => 'required|numeric|min:1',
        ]);
        $clientId = $request->client_id;
        $records = SaleNote::without(['user', 'soap_type', 'state_type', 'currency_type', 'payments'])
                            ->select('series', 'number', 'id', 'date_of_issue', 'total')
                            ->where('customer_id', $clientId)
                            ->whereNull('document_id')
                            ->whereIn('state_type_id', ['01', '03', '05'])
                            ->orderBy('number', 'desc');

        $dateOfIssue = $request->date_of_issue;
        $dateOfDue = $request->date_of_due;
        if ($dateOfIssue&&!$dateOfDue) {
            $records = $records->where('date_of_issue', $dateOfIssue);
        }

        if ($dateOfIssue&&$dateOfDue) {
            $records = $records->whereBetween('date_of_issue', [$dateOfIssue,$dateOfDue]);
        }
        $sum_total=0;
        $records = $records->take(20)
            ->get();
        $sum_total=number_format($records->sum('total'),2);
        return response()->json([
            'success' => true,
            'data' => $records,
            'sum_total' => $sum_total,
        ], 200);
    }

    public function getItemsFromNotes(Request $request)
    {
        $request->validate([
            'notes_id' => 'required|array',
        ]);


        if($request->select_all){

            $items = SaleNoteItem::whereIn('sale_note_id', $request->notes_id)->get();

        }else{

            $items = SaleNoteItem::whereIn('sale_note_id', $request->notes_id)
                    ->select('item_id', 'quantity')
                    ->get();
        }


        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200);
    }


    public function getConfigGroupItems()
    {
        return [
            'group_items_generate_document' => Configuration::select('group_items_generate_document')->first()->group_items_generate_document
        ];
    }

    /**
     * Proceso de duplicar una nota de venta por post
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function duplicate(Request $request)
    {
        // return $request->id;
        $params = $request->all();
        $obj = RecipesSubrecipe::query()->where("id",$params['id_to_duplicate'])->get(); // id de la subreceta     
        $item = Item::query()->where('id',$params['id'])->get(); // producto
        // dd($item[0]);
        $sale_price = $item[0]['has_igv'] == true ? $item[0]['sale_unit_price']:(float)$item[0]['sale_unit_price'] + ($item[0]['sale_unit_price']/1.18)*0.18;
        $sum1 = $this->suma1(json_decode($obj[0]['subrecipes_supplies']));
        $sum2 = $this->suma2(json_decode($obj[0]['cif'])); // cif
        $costs = json_decode($obj[0]['costs']);
        $costs->costs_unit_product =(float) floatval(round($sum1 + $sum2,5));
        
        $costs->margin_costs_soles = (float) floatval(round($sale_price - (float) $costs->costs_unit_product,5));
        $costs->margin_costs_procentage =(float) floatval(round((($sale_price - (float) $costs->costs_unit_product) / $sale_price) * 100,5));
        $data = [
            'id'=>null,
            'name'=> $item[0]['description'],
            'sale_price'=>$sale_price,
            'type_doc'=>'recipe',
            'quantity'=>$obj[0]['quantity'],
            'subrecipes_supplies'=>$obj[0]['subrecipes_supplies'],
            'cif'=>$obj[0]['cif'],
            'costs'=>json_encode($costs),
            'item_id'=> $params['id'],
            'created_at'=>date("Y-m-d H:i:s"),
            'updated_at'=>date("Y-m-d H:i:s"),
        ];   
        $this->recipes_subrecipes = RecipesSubrecipe::query()->updateOrCreate(['id' => $data['id']], $data);
        DB::connection('tenant')->commit();
        return [
            'success' => true,
            'data' => [
                'id' => "",
                'message'=>'Registro Agregar con exito!'
            ],
        ];

    }
    public function suma1($items){
        $total = 0;
        for ($i=0; $i < count($items) ; $i++) { 
            $total = $total + (float)$items[$i]->costs_by_grams;
        }
        return $total;
    }
    public function suma2($items){
        $total = 0;
        for ($i=0; $i < count($items) ; $i++) { 
            $total = $total + (float)$items[$i]->costs_total;
        }
        return $total;
    }
    


    /**
     * Retorna la vistsa para la configuracion de migracion avanzada en Nota de venta
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function SetAdvanceConfiguration(){
        $migrationConfiguration = MigrationConfiguration::getCollectionData();
        return view('tenant.configuration.sale_notes',compact('migrationConfiguration'));

    }

    /**
     * Guarda los datos para la migracion de nota de venta
     *
     * @param Request $request
     * @return array
     */
    public function SaveSetAdvanceConfiguration(Request $request){

        $data = $request->all();
        $data['success'] = false;
        $data['send_data_to_other_server'] = (bool)$data['send_data_to_other_server'];

        if(auth()->user()->type !=='admin'){
            $data['message'] = 'No puedes realizar cambios';
            return $data;
        }
        $configuration = Configuration::first();
        $migrationConfiguration = MigrationConfiguration::first();
        if(empty($migrationConfiguration)) $migrationConfiguration = new MigrationConfiguration($data);

        $migrationConfiguration->setUrl($data['url'])->setApiKey($data['apiKey'])->push();
        $configuration->setSendDataToOtherServer($data['send_data_to_other_server'] )->push();

        $data['url']=$migrationConfiguration->getUrl();
        $data['apiKey']=$migrationConfiguration->getApiKey();
        $data['send_data_to_other_server'] = $configuration->isSendDataToOtherServer();
        $data['success'] = true;
        $data['message'] = 'Ha sido acualizado';
        return $data;

    }


    /**
     * Retorna arreglo para generar nota de venta desde ecommerce
     *
     * @param Request $request
     * @return array
     */
    public function transformDataOrder(Request $request){

        $data = SaleNoteHelper::transformForOrder($request->all());

        return [
            'data' => $data
        ];

    }


    /**
     * Retorna items para generar json en checkout de hoteles
     *
     * @param Request $request
     * @return array
     */
    public function getItemsByIds(Request $request)
    {
        return SearchItemController::TransformToModalSaleNote(Item::whereIn('id', $request->ids)->get());
    }


    /**
     * Elimina la relación con factura (problema antiguo respecto un nuevo campo en notas de venta que se envía de forma incorrecta a la factura siendo esta rechazada)
     * No se previene el error en este metodo
     *
     *
     */
    public function deleteRelationInvoice(Request $request) {
        // dd($request->all());
        try {
            $sale_note = SaleNote::find($request->id);

            $document = Document::find($sale_note->document_id);
            $document->sale_note_id = null;
            $document->save();

            $sale_note->changed = 0;
            $sale_note->document_id = null;
            $sale_note->save();
        }catch(RequestException $e){
            return ['success' => false];
        }

        return ['success' => true];
    }
    public function export(Request $request){
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
        $type = $request->type_doc;
        $items = RecipesSubrecipe::query()->where("type_doc",$type);
        $extradata = [];

        if($period !== 'all'){
            $items->whereBetween('recipes_subrecipes.created_at', [$d_start, $d_end]);
        }
        $level = $type == "recipe" ?3:2 ;
        $records =  $items->get();
        return (new RecipesCostsExport())
            ->setExtraData($extradata)
            ->records($records)
            ->download("Reporte_Inventario_$level" .Carbon::now().'.xlsx');
    }
    public function sumTotals($params){
        $sum = 0 ;
        for ($i=0; $i < count($params) ; $i++) { 
            $sum = $sum + (float)$params[$i]->costs_total;
        }
        return $sum ;
    }
    public function sumTotalsRecipes($params){
        $sum = 0 ;
        $sum1 = 0 ;
        for ($i=0; $i < count($params) ; $i++) { 
            $sum = $sum + (float)$params[$i]->quantity;
            $sum1 = $sum1 + (float)$params[$i]->costs_by_grams;
        }
        return [$sum,$sum1] ;
    }
    public function export2(Request $request){
        
        $params = $request->query();
        $type = $request->type_doc;
        $items = RecipesSubrecipe::query()->where("id",$params['id']);
        $extradata = [];
        $records_ =  $items->get();
        $records_[0]['subrecipes_supplies'] = json_decode($records_[0]['subrecipes_supplies']);
        $records_[0]['cif'] = json_decode($records_[0]['cif']);
        $records_[0]['costs'] = json_decode($records_[0]['costs']);
        $records_[0]['sum_cif'] = $this->sumTotals($records_[0]['cif']);
        $sums = $this->sumTotalsRecipes($records_[0]['subrecipes_supplies']);
        $records_[0]['total_grams'] = $sums[0];
        $records_[0]['total_costs'] = $sums[1];
        $records = $records_[0];
        // dd($records);
        // return (new RecipesCostsExport2())
        //     ->setExtraData($extradata)
        //     ->records($records)
        //     ->download("Recetas" .Carbon::now().'.pdf');

        $pdf = PDF::loadView('myPDF', ["records"=>$records]);

  

        return $pdf->download("Recetas" .Carbon::now().'.pdf');
    }

}

<?php

    namespace App\Models\Tenant;

    use App\Models\Tenant\Catalogs\CurrencyType;
    use App\Models\Tenant\Catalogs\DocumentType;
    use App\Traits\SupplieTrait;
    use Carbon\Carbon;
    use Hyn\Tenancy\Traits\UsesTenantConnection;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\MorphMany;
    use Illuminate\Database\Query\Builder;
    use Modules\Item\Models\WebPlatform;
    use Modules\Order\Models\OrderNote;
    use Modules\Sale\Models\TechnicalService;
    use Modules\Pos\Models\Tip;
    use Modules\Sale\Models\Agent;

    /**
     * Class Supplie
     *
     * @package App\Models\Tenant\
     * @mixin ModelTenant
     * @property name                                           $name
     * @property sale_price                                     $sale_price
     * @property type_doc                                       $type_doc
     * @property quantity                                       $quantity
     * @property subrecipes_supplies                            $subrecipes_supplies
     * @property cif                                            $cif
     * @property costs                                          $costs
     * @property item_id                                          $item_id
     * @property Carbon|null                                    $updated_at
     * @property Carbon|null                                    $created_at
     * @method static Builder|Item                                  whereNotIsSet()
     * @method static \Illuminate\Database\Eloquent\Builder|SaleNote newModelQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|SaleNote newQuery()
     * @method static \Illuminate\Database\Eloquent\Builder|Supplie query()
     * @method static \Illuminate\Database\Eloquent\Builder|SaleNote whereNotChanged()
     * @method static \Illuminate\Database\Eloquent\Builder|SaleNote whereStateTypeAccepted()
     * @method static \Illuminate\Database\Eloquent\Builder|Supplie whereTypeUser()
     * @method static \Illuminate\Database\Eloquent\Builder|SaleNote WhereEstablishmentId()
     * @property-read int|null                                  $cash_documents_count
     * @property-read Collection|\App\Models\Tenant\GuideFile[] $guide_files
     * @property-read int|null                                  $guide_files_count
     * @property-read int|null                                  $kardexes_count
     * @property-read int|null                                  $sale_note_payments_count
     * @method static \Illuminate\Database\Eloquent\Builder|SaleNote whereEstablishmentId($establishment_id = 0)
     */
    class RecipesSubrecipe extends ModelTenant
    {
        use UsesTenantConnection;
        protected $table ='recipes_subrecipes';
        protected $fillable = [
            'name',
            'sale_price',
            'type_doc',
            'quantity',
            'subrecipes_supplies',
            'cif',
            'costs',
            'item_id',
            'created_at',
            'updated_at',
        ];

        public static function boot()
        {
            parent::boot();
            static::creating(function (self $model) {
            });

        }

        /**
         * Busca el ultimo numero basado en series y el prefijo.
         *
         * @param SaleNote $model
         *
         * @return int
         */
        public static function getLastNumberByModel(Supplie $model)
        {
            $sn = Supplie::where(
                [
                    'series' => $model->series,
                    'prefix' => $model->prefix,
                    // 'number',
                ])
                ->select('number')
                ->orderBy('number', 'desc')
                ->first();
            $return = 0;
            if ( !empty($sn)) {
                $return += $sn->number;
            }
            return $return + 1;
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function customer()
        {
            return $this->belongsTo(Person::class, 'customer_id');
        }

        /**
         * Obtiene la fecha de vencimiento
         *
         * @return mixed
         */
        public function getDueDate()
        {
            return $this->due_date;
        }

        /**
         * Establece la fecha de vencimiento
         *
         * @param mixed $due_date
         *
         * @return SaleNote
         */
        public function setDueDate($due_date)
        {
            $this->due_date = $due_date;
            return $this;
        }

        public function getEstablishmentAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setEstablishmentAttribute($value)
        {
            $this->attributes['establishment'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getCustomerAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setCustomerAttribute($value)
        {
            $this->attributes['customer'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getChargesAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setChargesAttribute($value)
        {
            $this->attributes['charges'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getDiscountsAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setDiscountsAttribute($value)
        {
            $this->attributes['discounts'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getPrepaymentsAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setPrepaymentsAttribute($value)
        {
            $this->attributes['prepayments'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getGuidesAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setGuidesAttribute($value)
        {
            $this->attributes['guides'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getRelatedAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setRelatedAttribute($value)
        {
            $this->attributes['related'] = (is_null($value)) ? null : json_encode($value);
        }
        /**
         * @param Builder $query
         *
         * @return Builder
         */
        public function scopeWhereNotIsSet($query)
        {
            return $query->where('is_set', false);
        }
        public function getPerceptionAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setPerceptionAttribute($value)
        {
            $this->attributes['perception'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getDetractionAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setDetractionAttribute($value)
        {
            $this->attributes['detraction'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getLegendsAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setLegendsAttribute($value)
        {
            $this->attributes['legends'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getPointSystemDataAttribute($value)
        {
            return (is_null($value)) ? null : (object)json_decode($value);
        }

        public function setPointSystemDataAttribute($value)
        {
            $this->attributes['point_system_data'] = (is_null($value)) ? null : json_encode($value);
        }

        public function getIdentifierAttribute()
        {
            return $this->prefix . '-' . $this->id;
        }

        /**
         * @return BelongsTo
         */
        public function agent()
        {
            return $this->belongsTo(Agent::class);
        }

        /**
         * @return BelongsTo
         */
        public function user()
        {
            return $this->belongsTo(User::class);
        }

        /**
         * @return BelongsTo
         */
        public function seller()
        {
            return $this->belongsTo(User::class,'seller_id');
        }

        /**
         * @return BelongsTo
         */
        public function soap_type()
        {
            return $this->belongsTo(SoapType::class);
        }

        /**
         * @return BelongsTo
         */
        public function establishment()
        {
            return $this->belongsTo(Establishment::class);
        }

        /**
         * @return BelongsTo
         */
        public function state_type()
        {
            return $this->belongsTo(StateType::class);
        }

        /**
         * @return BelongsTo
         */
        public function person()
        {
            return $this->belongsTo(Person::class, 'customer_id');
        }

        /**
         * @return BelongsTo
         */
        public function currency_type()
        {
            return $this->belongsTo(CurrencyType::class, 'currency_type_id');
        }

        /**
         * @return HasMany
         */
        public function items()
        {
            return $this->hasMany(SaleNoteItem::class);
        }

        /**
         * @return HasMany
         */
        public function kardex()
        {
            return $this->hasMany(Kardex::class);
        }

        /**
         * Se usa en la relacion con el inventario kardex en modules/Inventory/Traits/InventoryTrait.php.
         * Tambien se debe tener en cuenta modules/Inventory/Providers/InventoryKardexServiceProvider.php y
         * app/Providers/KardexServiceProvider.php para la correcta gestion de kardex
         *
         * @return MorphMany
         */
        public function inventory_kardex()
        {
            return $this->morphMany(InventoryKardex::class, 'inventory_kardexable');
        }

        /**
         * @return HasMany
         */
        public function documents()
        {
            return $this->hasMany(Document::class);
        }

        /**
         * @return BelongsTo
         * order from ecommerce
         */
        public function order()
        {
            return $this->belongsTo(Order::class);
        }

        /**
         * @return BelongsTo
         */
        public function technical_service()
        {
            return $this->belongsTo(TechnicalService::class);
        }

        /**
         * @return BelongsTo
         */
        public function relation_establishment()
        {
            return $this->belongsTo(Establishment::class, 'establishment_id');
        }

        /**
         * @return mixed
         */
        public function getNumberToLetterAttribute()
        {
            $legends = $this->legends;
            $legend = collect($legends)->where('code', '1000')->first();
            return $legend->value;
        }

        /**
         * @return string
         */
        public function getNumberFullAttribute()
        {
            $number_full = ($this->series && $this->number) ? $this->series . '-' . $this->number : $this->prefix . '-' . $this->id;

            return $number_full;
        }


        /**
         * @param $query
         *
         * @return null
         */
        public function scopeWhereTypeUser($query, $params= [])
        {
            if(isset($params['user_id'])) {
                $user_id = (int)$params['user_id'];
                $user = User::find($user_id);
                if(!$user) {
                    $user = new User();
                }
            }
            else {
                $user = auth()->user();
            }
            return ($user->type == 'seller') ? $query->where('user_id', $uxser->id) : null;
        }


        /**
         * @param $query
         *
         * @return mixed
         */
        public function scopeWhereStateTypeAccepted($query)
        {
            return $query->whereIn('state_type_id', ['01', '03', '05', '07', '13']);
        }

        /**
         * @param $query
         *
         * @return mixed
         */
        public function scopeWhereNotChanged($query)
        {
            return $query->where('changed', false);
        }

        /**
         * @return BelongsTo
         */
        public function quotation()
        {
            return $this->belongsTo(Quotation::class);
        }

        /**
         * @return BelongsTo
         */
        public function payment_method_type()
        {
            return $this->belongsTo(PaymentMethodType::class);
        }


        /**
         *
         * Validar condicion para el boton edicion
         *
         * @param  int $total_documents
         * @return bool
         *
         */
        public function getBtnGenerate($total_documents)
        {
            if($total_documents > 0)
            {
                $btn_generate = false;
            }
            else
            {
                // si proviene de un pedido o registro externo que afecta inventario se deshabilita la opcion editar
                // si se habilita se deben controlar los movimientos que afectan a inventario
                if($this->isGeneratedFromExternalRecord())
                {
                    $btn_generate = false;
                }
                else
                {
                    $btn_generate = true;
                }
            }

            return $btn_generate;
        }

        /**
         * @return Collection
         */
        public function getTransformPayments()
        {

            $payments = $this->payments()->get();
            return $payments->transform(function ($row, $key) {
                /** @var SaleNotePayment $row */
                return [
                    'id' => $row->id,
                    'sale_note_id' => $row->sale_note_id,
                    'date_of_payment' => $row->date_of_payment->format('Y-m-d'),
                    'payment_method_type_id' => $row->payment_method_type_id,
                    'has_card' => $row->has_card,
                    'card_brand_id' => $row->card_brand_id,
                    'reference' => $row->reference,
                    'payment' => $row->payment,
                    'payment_method_type' => $row->payment_method_type,
                    'payment_destination_id' => ($row->global_payment) ? ($row->global_payment->type_record == 'cash' ? 'cash' : $row->global_payment->destination_id) : null,
                    'payment_filename' => ($row->payment_file) ? $row->payment_file->filename : null,
                ];
            });
        }

        /**
         * @return HasMany
         */
        public function payments()
        {
            return $this->hasMany(SaleNotePayment::class);
        }

        /**
         * Devuelve una coleccion de plataformas web basado en los items.
         *
         * @return \Illuminate\Database\Eloquent\Builder[]|Collection|Builder[]|\Illuminate\Support\Collection|mixed|WebPlatform|WebPlatform[]
         */
        public function getPlatformThroughItems()
        {

            /**
             * @var Collection  $items
             * @var WebPlatform $web_platforms
             */
            $items = $this->items->pluck('item_id');
            $web_platform_table_name = (new WebPlatform())->getTable();
            $item_table_name = (new Item())->getTable();
            return WebPlatform::leftJoin('items', "$web_platform_table_name.id", '=', "$item_table_name.web_platform_id")
                ->select("$web_platform_table_name.id", "$web_platform_table_name.name")
                ->wherein("$item_table_name.id", $items)
                ->get();
        }
        /**
         * Devuelve el vendedor asociado, Si seller id es nulo, devolverá el usuario del campo user.
         *
         * @return User
         */
        public function getSellerData()
        {
            if ( !empty($this->seller_id)) {
                return $this->seller;
            }
            return $this->user;

        }
        public static function FormatNumber($number, $decimal = 2)
        {
            return number_format($number, $decimal);
        }

        /**
         * Genera la estructura necesaria para exportar por api
         *
         * @return array
         */
        public function getDataToApiExport()
        {

            $date_of_issue = ($this->date_of_issue) ? $this->date_of_issue->format('Y-m-d') : '';
            $sale_note_items = SaleNoteItem::where('sale_note_id', $this->id)->get();
            $items = [];
            foreach ($sale_note_items as $item) {
                /** @var SaleNoteItem $item */
                $tem_item = [];
                $tem_item['id'] = $item->id;
                $tem_item['currency_type_id'] = $item->currency_type_id;
                $tem_item['quantity'] = $item->quantity;
                $tem_item['unit_value'] = $item->unit_value;
                $tem_item['affectation_igv_type_id'] = $item->affectation_igv_type_id;
                $tem_item['total_base_igv'] = $item->total_base_igv;
                $tem_item['percentage_igv'] = $item->percentage_igv;
                $tem_item['total_igv'] = $item->total_igv;
                $tem_item['system_isc_type_id'] = $item->system_isc_type_id;
                $tem_item['total_base_isc'] = $item->total_base_isc;
                $tem_item['percentage_isc'] = $item->percentage_isc;
                $tem_item['total_isc'] = $item->total_isc;
                $tem_item['total_base_other_taxes'] = $item->total_base_other_taxes;
                $tem_item['percentage_other_taxes'] = $item->percentage_other_taxes;
                $tem_item['total_other_taxes'] = $item->total_other_taxes;
                $tem_item['total_plastic_bag_taxes'] = $item->total_plastic_bag_taxes;
                $tem_item['total_taxes'] = $item->total_taxes;
                $tem_item['price_type_id'] = $item->price_type_id;
                $tem_item['unit_price'] = $item->unit_price;
                $tem_item['total_value'] = $item->total_value;
                $tem_item['total_discount'] = $item->total_discount;
                $tem_item['total_charge'] = $item->total_charge;
                $tem_item['total'] = $item->total;
                $tem_item['attributes'] = $item->attributes;
                $tem_item['charges'] = $item->charges;
                $tem_item['discounts'] = $item->discounts;
                $tem_item['affectation_igv_type'] = $item->affectation_igv_type;
                $it = $item->item;
                $ot = Item::find($it->id);
                $item_select = Item::where('id', $it->id)->select(
                    'name',
                    'second_name',
                    'description',
                    'model',
                    'technical_specifications',
                    'item_type_id',
                    'item_code',
                    'date_of_due',
                    'account_id',
                    'item_code_gs1',
                    'unit_type_id',
                    'currency_type_id',
                    'sale_unit_price',
                    'purchase_has_igv',
                    'has_igv',
                    'amount_plastic_bag_taxes',
                    'sale_affectation_igv_type_id',
                    'purchase_affectation_igv_type_id',
                    'calculate_quantity',
                    'is_set',
                    'has_plastic_bag_taxes',
                    'lot_code',
                    'lots_enabled',
                    'series_enabled',
                    'attributes',
                    'web_platform_id',
                    'warehouse_id',
                    'status',
                    'cod_digemid',
                    'sanitary'
                )->first();
                $tem_item['full_item'] = $item_select !== null ? $item_select->toArray() : [];
                $property = [
                    'full_description',
                    'name',
                    'description',
                    'currency_type_id',
                    'internal_id',
                    'item_code',
                    'currency_type_symbol',
                    'sale_unit_price',
                    'purchase_unit_price',
                    'unit_type_id',
                    'sale_affectation_igv_type_id',
                    'purchase_affectation_igv_type_id',
                    'calculate_quantity',
                    'has_igv',
                    'is_set',
                    'aux_quantity',
                    'brand',
                    'category',
                    'stock',
                    'image',
                    'warehouses',
                    'unit_price',
                    'presentation',
                ];
                $t_it = [
                    'id' => property_exists($it, 'id') ? $it->id : $ot->id,
                    'item_id' => property_exists($it, 'id') ? $it->id : $ot->id,

                ];
                for ($i = 0; $i < count($property); $i++) {
                    $w = $property[$i];
                    $t_it[$w] = property_exists($it, $w) ? $it->{$w} : $ot->{$w};
                }
                $tem_item['item'] = $t_it;
                $items[] = $tem_item;
            }
            $payments_model = SaleNotePayment::where('sale_note_id', $this->id)->get();
            $payments = [];

            foreach ($payments_model as $payment) {
                /** @var SaleNotePayment $payment */
                $payments[] = $payment->toArray();
            }
            $attributes = $this->attributes;

            $customer = Person::find($this->customer_id);
            if (empty($customer->identity_document_type_id)) $customer->identity_document_type_id = 6;
            if (empty($customer->country_id)) $customer->country_id = 'PE';
            if (empty($customer->district_id)) $customer->district_id = '';
            $customer->codigo_tipo_documento_identidad = $customer->identity_document_type_id;
            $customer->numero_documento = $customer->number;
            $customer->apellidos_y_nombres_o_razon_social = $customer->name;
            $customer->codigo_pais = $customer->country_id;
            $customer->ubigeo = $customer->district_id;
            $customer->direccion = $customer->address;
            $customer->correo_electronico = $customer->email;
            $customer->telefono = $customer->telephone;
            $datos_del_cliente_o_receptor = $customer->toArray();
            $empty_ob = (object)[];
            $data = [

                'prefix' => $this->prefix,
                'series_id' => 10,
                'establishment_id' => null,
                'date_of_issue' => $date_of_issue,
                'time_of_issue' => $this->time_of_issue,
                'customer_id' => $this->customer_id,
                'currency_type_id' => $this->currency_type_id,
                'purchase_order' => $this->purchase_order,
                'exchange_rate_sale' => $this->exchange_rate_sale,
                'total_prepayment' => $this->total_prepayment,
                'total_charge' => $this->total_charge,
                'total_discount' => $this->total_discount,
                'total_free' => $this->total_free,
                'total_exportation' => $this->total_exportation,
                'total_taxed' => $this->total_taxed,
                'total_unaffected' => $this->total_unaffected,
                'total_exonerated' => $this->total_exonerated,
                'total_igv' => $this->total_igv,
                'total_base_isc' => $this->total_base_isc,
                'total_isc' => $this->total_isc,
                'total_base_other_taxes' => $this->total_base_other_taxes,
                'total_other_taxes' => $this->total_other_taxes,
                'total_taxes' => $this->total_taxes,
                'total_value' => $this->total_value,
                'total' => $this->total,
                'operation_type_id' => $this->operation_type_id,
                'items' => $items,
                'force_create_if_not_exist' => true,

                'charges' => $this->charge,
                'attributes' => $attributes,
                'guides' => null,
                // 'guides'                 => isset($attributes['guides']) ? $attributes['guides'] : $empty_ob,
                'discounts' => isset($attributes['discounts']) ? $attributes['discounts'] : $empty_ob,
                'payments' => $payments,
                'additional_information' => $this->additional_information,

                'actions' => [],
                'apply_concurrency' => (bool)$this->apply_concurrency,
                'type_period' => $this->type_period,
                'quantity_period' => $this->quantity_period,
                'automatic_date_of_issue' => $this->automatic_date_of_issue,
                'enabled_concurrency' => $this->enabled_concurrency,
                'datos_del_cliente_o_receptor' => $datos_del_cliente_o_receptor,

            ];

            $data['quantity_period'] = (int)$data['quantity_period'];
            $data['apply_concurrency'] = (bool)$data['apply_concurrency'];
            $data['enabled_concurrency'] = (bool)$data['enabled_concurrency'];

            return $data;
        }

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function guide_files()
        {
            return $this->hasMany(GuideFile::class);
        }

        /**
         * @param \Illuminate\Database\Eloquent\Builder $query
         * @param int                                   $establishment_id
         *
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function scopeWhereEstablishmentId(\Illuminate\Database\Eloquent\Builder $query, $establishment_id = 0)
        {

            if ($establishment_id != 0) {
                $query->where('establishment_id', $establishment_id);
            }
            return $query;
        }


        /**
         * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
         */
        public function order_note()
        {
            return $this->belongsTo(OrderNote::class);
        }


        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function cash_documents()
        {
            return $this->hasMany(CashDocument::class);
        }


        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function kardexes()
        {
            return $this->hasMany(Kardex::class);
        }


        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasMany
         */
        public function sale_note_payments()
        {
            return $this->hasMany(SaleNotePayment::class);
        }

        public function tip()
        {
            return $this->morphOne(Tip::class, 'origin');
        }

        /**
         *
         * Filtros para reportes de comisiones
         * Usado en:
         * Modules\Report\Http\Controllers\ReportCommissionController
         *
         * @param \Illuminate\Database\Eloquent\Builder $query
         * @param $date_start
         * @param $date_end
         * @param $establishment_id
         * @param $user_type
         * @param $user_seller_id
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function scopeWhereFilterCommission($query, $date_start, $date_end, $establishment_id, $user_type, $user_seller_id, $row_user_id){

            $query->whereStateTypeAccepted()
                    ->whereBetween('date_of_issue', [$date_start, $date_end])
                    ->whereEstablishmentId($establishment_id);

            if($user_seller_id){
                $query->where($user_type, $user_seller_id);
            }else{
                $query->where($user_type, $row_user_id);
            }

            return $query;
        }

        /**
         * @return string|null
         */
        public function getGrade(): ?string
        {
            return $this->grade;
        }

        /**
         * @param string|null $grade
         *
         * @return SaleNote
         */
        public function setGrade(?string $grade): Supplie
        {
            $this->grade = $grade;
            return $this;
        }

        /**
         * @return string|null
         */
        public function getSection(): ?string
        {
            return $this->section;
        }

        /**
         * @param string|null $section
         *
         * @return SaleNote
         */
        public function setSection(?string $section): Supplie
        {
            $this->section = $section;
            return $this;
        }

        /**
         * Devuelve el modelo del tipo de documetno actual
         *
         * @return DocumentType
         */
        public function getDocumentType(){
            return DocumentType::find('80');
        }


        /**
         *
         * Filtros para reporte utilidades
         * Usado en:
         * DashboardUtility - Obtener total descuentos globales
         *
         * @param \Illuminate\Database\Eloquent\Builder $query
         * @param $establishment_id
         * @param $d_start
         * @param $d_end
         * @param $item_id
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function scopeWhereFilterDashboardUtility($query, $establishment_id, $d_start, $d_end, $item_id)
        {

            $query->where([['establishment_id', $establishment_id], ['changed', false]])->whereStateTypeAccepted();

            if($d_start && $d_end) $query->whereBetween('date_of_issue', [$d_start, $d_end]);

            if($item_id)
            {
                $query->whereHas('items', function($q) use($item_id){
                    $q->where('item_id', $item_id);
                });
            }

            return $query;
        }


        /**
         *
         * Obtener notas de venta filtradas por el id de los items (SaleNoteItem)
         *
         * Usado en:
         * DashboardUtility - Obtener totales
         *
         * @param \Illuminate\Database\Eloquent\Builder $query
         * @param array $sale_note_ids
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function scopeWhereRecordsByItems($query, $sale_note_ids)
        {
            return$query->withOut(['user', 'soap_type', 'state_type', 'currency_type', 'items', 'payments'])
                        ->whereIn('id', $sale_note_ids)
                        ->select('id', 'total', 'currency_type_id', 'exchange_rate_sale');

        }


        /**
         *
         * Obtener total y realizar conversión al tipo de cambio si se requiere
         *
         * @return float
         */
        public function getTransformTotal()
        {
            return ($this->currency_type_id === 'PEN') ? $this->total : ($this->total * $this->exchange_rate_sale);
        }


        /**
         *
         * Filtro para no incluir relaciones en consulta
         *
         * @param \Illuminate\Database\Eloquent\Builder $query
         * @return \Illuminate\Database\Eloquent\Builder
         */
        public function scopeWhereFilterWithOutRelations($query)
        {
            return $query->withOut([
                'user',
                'soap_type',
                'state_type',
                'currency_type',
                'items',
                'payments'
            ]);
        }


        /**
         *
         * Obtener vuelto para mostrar en pdf
         *
         * @return float
         */
        public function getChangePayment()
        {
            return ($this->total - $this->payments->sum('payment')) - $this->payments->sum('change');
        }

        /**
         *
         * Obtener porcentaje de cargos para mostrar en pdf
         *
         * @return float
         */
        public function getTotalFactor()
        {
            $total_factor = 0;

            if($this->charges)
            {
                $total_factor = collect($this->charges)->sum('factor') * 100;
            }

            return $total_factor;
        }


        /**
         *
         * Filtrar por rango de fechas
         *
         * @param \Illuminate\Database\Eloquent\Builder $query
         * @return \Illuminate\Database\Eloquent\Builder
         *
         */
        public function scopeFilterRangeDateOfIssue($query, $date_start, $date_end)
        {
            return $query->whereBetween('date_of_issue', [$date_start, $date_end]);
        }


        /**
         *
         * Obtener la fecha de vencimiento y aplicar formato
         *
         * @return string
         */
        public function getFormatDueDate()
        {
            return $this->due_date ? $this->generalFormatDate($this->due_date) : null;
        }


        /**
         *
         * Obtener descripción del tipo de documento
         *
         * @return string
         */
        public function getDocumentTypeDescription()
        {
            return 'NOTA DE VENTA';
        }


        /**
         *
         * Obtener pagos en efectivo
         *
         * @return Collection
         */
        public function getCashPayments()
        {
            return $this->payments()->whereFilterCashPayment()->get()->transform(function($row){{
                return $row->getRowResourceCashPayment();
            }});
        }


        /**
         *
         * Validar si el registro esta rechazado o anulado
         *
         * @return bool
         */
        public function isVoidedOrRejected()
        {
            return in_array($this->state_type_id, self::VOIDED_REJECTED_IDS);
        }


        /**
         *
         * Retornar el total de pagos
         *
         * @return float
         */
        public function getTotalAllPayments()
        {

            $total_payments = 0;

            if(!$this->isVoidedOrRejected())
            {
                $total_payments = $this->payments->sum('payment');

                if($this->currency_type_id === 'USD')
                {
                    $total_payments = $this->generalConvertValueToPen($total_payments, $this->exchange_rate_sale);
                }
            }

            return $total_payments;
        }


        /**
         *
         * Validar si la nota de venta fue generada a partir de un registro externo
         *
         * Usado en:
         * SaleNoteController
         *
         * @return bool
         */
        public function isGeneratedFromExternalRecord()
        {
            $generated = false;

            if(!is_null($this->order_note_id))
            {
                $generated = true;
            }

            // @todo agregar mas registros relacionados

            return $generated;
        }


        /**
         *
         * Obtener url para impresión
         *
         * @param  string $format
         * @return string
         */
        public function getUrlPrintPdf($format = "a4")
        {
            return url("sale-notes/print/{$this->external_id}/{$format}");
        }


        /**
         *
         * Obtener relaciones necesarias o aplicar filtros para reporte pagos - finanzas
         *
         * @param  Builder $query
         * @return Builder
         */
        public function scopeFilterRelationsGlobalPayment($query)
        {
            return $query->whereFilterWithOutRelations()
                        ->select([
                            'id',
                            'user_id',
                            'external_id',
                            'establishment_id',
                            'soap_type_id',
                            'state_type_id',
                            'prefix',
                            'date_of_issue',
                            'time_of_issue',
                            'customer_id',
                            'customer',
                            'currency_type_id',
                            'exchange_rate_sale',
                            'total',
                            'filename',
                            'total_canceled',
                            'quotation_id',
                            'order_note_id',
                            'series',
                            'number',
                            'paid',
                            'payment_method_type_id',
                            'due_date',
                            'document_id',
                            'seller_id',
                            'order_id',
                            'technical_service_id',
                            'changed',
                            'user_rel_suscription_plan_id',
                            'subtotal',
                        ]);

        }


        /**
         *
         * Determina si fue creado desde pos
         *
         * @return bool
         */
        public function isCreatedFromPos()
        {
            return $this->created_from_pos;
        }


        /**
         *
         * Determina si fue usado para sistema por puntos
         *
         * @return bool
         */
        public function isPointSystem()
        {
            return $this->point_system;
        }


        /**
         *
         * Obtener puntos por la venta
         *
         * @return float
         *
         */
        public function getPointsBySale()
        {
            $calculate_quantity_points = 0;

            if($this->isPointSystem())
            {
                $point_system_data = $this->point_system_data;
                $total = $this->total;

                $value_quantity_points = ($total / $point_system_data->point_system_sale_amount) * $point_system_data->quantity_of_points;
                $calculate_quantity_points = $point_system_data->round_points_of_sale ? intval($value_quantity_points) : round($value_quantity_points, 2);
            }

            return $calculate_quantity_points;
        }

    }

<template>
    <div v-if="recipeId != 'duplicate'">

        <el-dialog :close-on-click-modal="false" :title="titleDialog" :visible="showDialog" append-to-body class="pt-0"
            top="7vh" width="90%" @close="close" @open="create">
                <form autocomplete="off" @submit.prevent="submit">
                    <div class="container">
                        <div class="row">
                            <label>Nombre:</label>
                            <div v-if="recipeId != 'recipe'" class="col">
                                <el-input v-model="form_recipes_subrecipes.name.description" dusk="name"></el-input>
                                <small v-if="errors.name" class="form-control-feedback" v-text="errors.name[0]"></small>
                            </div>
                            <div v-if="recipeId == 'recipe'" class="col">
                                <template id="select-append">
                                    <el-select 
                                        id="select-width"
                                        ref="selectSearchNormal"
                                        slot="prepend"
                                        tabindex="1"
                                        :loading="loading_search"
                                        filterable
                                        remote
                                        @focus="focusSelectItem"

                                        v-model="form_recipes_subrecipes.name.id" 
                                        :remote-method="searchRemoteItems">
                                            <el-tooltip
                                                v-for="option in records_items"
                                                :key="option.id"
                                                placement="left">
                                                
                                                <el-option
                                                    :label="option.description"
                                                    :value="option.id"
                                                ></el-option>
                                            </el-tooltip>

                                        <!-- <el-option v-for="item in records_items"
                                            :label="item.description"
                                            :key="item.id"
                                            :value="item.id"
                                            ></el-option>-->
                                    </el-select> 
                                </template>
                            </div>
                            <div v-if="recipeId == 'recipe' && recordId == null" class="col">
                                <label>Precio de Venta S/:</label>
                                <el-input dusk="price_sale" :value="calculatePriceItem()" disabled></el-input>
                            </div>
                            <div v-if="recipeId == 'recipe' && recordId " class="col">
                                <label>Precio de Venta S/:</label>
                                <el-input dusk="price_sale" :value="form_recipes_subrecipes.sale_price" disabled></el-input>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="container">
                        <div class="row">
                            <div class="col">
                                <div>Items incluidos en la Receta/Sub-Receta</div>
                                <button class="btn btn-info btn-sm border border-3" type="submit"
                                    v-bind:style="{ color: supplies, fontSize: suppliesFontSize + 'px' }"
                                    @click.prevent="changeSupplies()">Insumos</button>
                                <button class="btn btn-info btn-sm border border-3" v-if="recipeId == 'recipe'" type="submit"
                                    v-bind:style="{ color: recipeSub, fontSize: recipeSubFontSize + 'px' }"
                                    @click.prevent="changeSubRecipe()">Sub-Receta</button>
                                <div class="container">
                                    <div class="row">
                                        <div class="col-6">
                                            <div :class="{ 'has-danger': errors.description }" class="form-group">
                                                <label class="control-label">Item<span class="text-danger">*</span></label>
                                                <select v-model="itemSelected" class="form-control form-control-sm mb-1">
                                                    <option v-for="item in recordsSelect" :value="item"> {{ item.name }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            Unidad
                                            <select class="form-control form-control-sm">
                                                <!-- <option> <strong>{{ itemSelected?itemSelected.unit : 'Not Select'}}</strong> -->
                                                <option> <strong>{{ itemSelected?itemSelected.unit == 'Kilogramo'?'Gramos':itemSelected.unit:'Not Select'}}</strong>
                                                </option>

                                            </select>
                                        </div>
                                        <div class="col">
                                            Cantidad
                                            <el-input v-model="cost_by_item" dusk="amount"></el-input>
                                            <small v-if="errors.amount" class="form-control-feedback"
                                                v-text="errors.amount[0]"></small>
                                        </div>
                                    </div>

                                </div>
                                <div class="container">
                                    <div class="row">
                                        <div class="col">
                                            <div :class="{ 'has-danger': errors.costs_by_grams }" class="form-group">
                                                <label class="control-label">Costo por gramo S/ <span
                                                        class="text-danger">*</span></label>
                                                <el-input v-model="form.costs_by_grams" dusk="costs_by_grams"
                                                    :value="getValueCostByGrams()" disabled></el-input>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div :class="{ 'has-danger': errors.costs_by_item }" class="form-group">
                                                <label class="control-label">Costo por Item S/ <span
                                                        class="text-danger">*</span></label>
                                                <el-input v-model="form.costs_by_item" dusk="sale_unit_price"
                                                    :value="form.costs_by_item ? form.costs_by_item : 0" disabled></el-input>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="form-actions text-right pt-2 mt-2">
                                                <el-button :loading="loading_submit" @click.prevent="addRecipes"
                                                    type="primary">Agregar
                                                </el-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th v-for="column in columns_recipes" scope="col">{{ column }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(value, index) in recordsRecipes">
                                            <th scope="row"> {{ index + 1 }}</th>
                                            <td>{{ value.name ? value.name : value.name_supplie_subrecipe }}</td>
                                            <td>{{ value.quantity }}</td>
                                            <td>{{ value.unit }}</td>
                                            <td>{{ value.costs_by_grams }}</td>
                                            <td><span type="button" class="badge badge-danger"
                                                    @click.prevent="deleteRecipes(index)">Eliminar</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div>
                                    <label for=""> <span> Total de Gramos: {{ totals_recipes_subrecipes.total_grams }} grs Total Costo: S/ {{ totals_recipes_subrecipes.total_costs }} </span> </label>
                                </div>
                            </div>
                            <div class="col" style="border-left: 6px solid #4597df ; height: 450px;">
                                <div>CIF:</div>
                                <div class="container">
                                    <div class="row">
                                        <div class="col">
                                            <div :class="{ 'has-danger': errors.description }" class="form-group">
                                                <label class="control-label">Item Costo Indirecto<span
                                                        class="text-danger">*</span></label>
                                                <select v-model="form_cif.name" class="form-control form-control-sm">
                                                    <option> Servicio de Luz</option>
                                                    <option> Servicio de agua</option>
                                                    <option> Mano de Obra</option>
                                                    <option> Alquiler</option>
                                                    <option> Gas</option>
                                                    <option> Otro</option>
                                                </select>

                                            </div>
                                        </div>
                                        <div class="col">
                                            <label class="control-label">Gasto Mensual S/<span
                                                    class="text-danger">*</span></label>
                                            <el-input v-model="spent_month" dusk="spent_month"></el-input>
                                        </div>
                                        <div class="w-100"></div>
                                        <div class="col">
                                            <label class="control-label">Hrs trabajadas al dia<span
                                                    class="text-danger">*</span></label>
                                            <el-input v-model="hours_work_day" dusk="hours_work_day"></el-input>
                                        </div>
                                        <div class="col">
                                            <label class="control-label">Hrs útiles en el Proceso<span
                                                    class="text-danger">*</span></label>
                                            <el-input v-model="hours_util_process" dusk="hours_util_process"></el-input>
                                        </div>
                                        <div class="w-100"></div>
                                        <div class="col mb-1">
                                            <label class="control-label">Costo Total de Item</label>
                                            <el-input v-model="costs_total_item" :value="form_cif.costs_total_item"
                                                dusk="hours_work_day" disabled></el-input>
                                        </div>
                                        <div class="col">
                                            <div class="form-actions text-right pt-2 mt-2">
                                                <el-button :loading="loading_submit" @click.prevent="addCIF"
                                                    type="primary">Agregar
                                                </el-button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th v-for="column in columns_cif" scope="col">{{ column }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(value, index) in recordsCIF">
                                            <th scope="row">{{ index + 1}}</th>
                                            <th>{{ value.name }}</th>
                                            <th>{{ value.spent_month }}</th>
                                            <th>{{ value.hours_work_day }}</th>
                                            <th>{{ value.hours_util_process }}</th>
                                            <th>{{ value.costs_total }}</th>
                                            <td><span type="button" class="badge badge-danger"
                                                    @click.prevent="deleteCif(index)">Eliminar</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div>
                                <label for=""> <span> Total CIF: {{ totals_cif }} </span> </label>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row justify-content-md-center">
                            <div >
                                <label style="color: #4597df;text-align:end;" for="">Suma de Costo Total + Total CIF = {{ totals_cif + totals_recipes_subrecipes.total_costs }} </label>
                            </div>
                        </div>

                    </div>

                    <div v-if="recipeId == 'recipe'" class="container">
                        <div class="row align-items-center">
                            <div class="col-5">
                                <strong>RESULTADOS:</strong>
                                <div class="row no-gutters">
                                    <div class="mb-1 col-sm-6 col-md-8">Costo Unitario del Producto:</div>
                                    <div class="col-6 col-md-4"><span> <strong>{{ form_costs.costs_unit_product }}</strong>
                                        </span></div>

                                    <div class="mb-1 col-sm-6 col-md-8"></div>
                                    <div class="col-6 col-md-4"><span> <strong></strong> </span></div>

                                    <div class="mb-1 col-sm-6 col-md-8">Margen C. en Soles:</div>
                                    <div class="col-6 col-md-4"><span> <strong>{{ form_costs.margin_costs_soles }}</strong>
                                        </span></div>

                                    <div class="mb-1 col-sm-6 col-md-8">Margen C. en Porcentaje:</div>
                                    <div class="col-6 col-md-4"><span> <strong>{{ form_costs.margin_costs_procentage }} %</strong> </span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                

                    <div class="form-actions text-right pt-2 mt-2">
                        <el-button @click.prevent="close()">Cancelar</el-button>
                        <el-button :loading="loading_submit" native-type="submit" @click.prevent="submit" type="primary">Guardar
                        </el-button>
                    </div>
                </form>
    </el-dialog>
    </div>
    
    <div v-else>
        <el-dialog :close-on-click-modal="false" :title="titleDialog" :visible="showDialog" append-to-body class="pt-0"
            top="7vh" width="50%" @close="close" @open="create">
            <form autocomplete="off" >
                <label for="">Seleccione el producto que le va asignar al duplicado:</label>
                <div class="container">
                        <div class="row">
                            <label>Nombre:</label>
                            <div class="col">
                                <el-select v-model="form_recipes_subrecipes.name.id">
                                    <el-option v-for="item in records_items"
                                        :label="item.description"
                                        :key="item.id"
                                        :value="item.id"
                                        ></el-option>
                            </el-select>
                            </div>
                            <div class="col">
                                <label>Precio de Venta S/:</label>
                                <el-input dusk="price_sale" :value="calculatePriceItem()" disabled></el-input>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions text-right pt-2 mt-2">
                        <el-button @click.prevent="close()">Cancelar</el-button>
                        <el-button :loading="loading_submit" native-type="submit" @click.prevent="duplicar()" type="primary">Duplicar
                        </el-button>
                    </div>
            </form>
        </el-dialog>

    </div>
</template>

<script>
import { mapActions, mapState } from "vuex";
import { ItemOptionDescription, ItemSlotTooltip } from "../../../helpers/modal_item";
import queryString from "query-string";


export default {
    props: [
        'showDialog',
        'recordId',
        'recipeId', // recipe o sub recipe - receta o subreceta
        'external',
        'type',
        'pharmacy',
    ],
    components: {
    },
    computed: {

        ...mapState([
            'colors',
            'CatItemSize',
            'CatItemUnitsPerPackage',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemMoldCavity',
            'CatItemProductFamily',
            'config',
        ]),
        isService: function () {
            // Tener en cuenta que solo oculta las pestañas para tipo servicio.
            if (this.form !== undefined) {
                // Es servicio por selección
                if (this.form.unit_type_id !== undefined && this.form.unit_type_id === 'ZZ') {
                    if (
                        this.activeName == 'second' ||
                        this.activeName == 'third' ||
                        this.activeName == 'five'
                    ) {
                        this.activeName = 'first';
                    }
                    return true;
                }
            }
            return false;
        },
        canSeeProduction: function () {
            if (this.config && this.config.production_app) return this.config.production_app
            return false;
        },

    },

    data() {
        return {
            loading_search: false,
            showDialogLots: false,
            form_category: { add: false, name: null, id: null },
            form_brand: { add: false, name: null, id: null },
            warehouses: [],
            items: [],
            loading_submit: false,
            showPercentagePerception: false,
            has_percentage_perception: false,
            percentage_perception: null,
            enabled_percentage_of_profit: false,
            titleDialog: null,
            resource: 'recipescosts',
            resource_temp: '',
            errors: {},
            item_suplly: {},
            headers: headers_token,

            form_recipes_subrecipes: { id: null, name: {description:null,id:null}, sale_price: 0 } ,
            form_cif: { name: null, spent_month: 0, hours_work_day: 0, hours_util_process: 0, costs_total_item: 0 },
            form_costs: { id: null, costs_unit_product: 0, margin_costs_soles: 0, margin_costs_procentage: 0 },

            form: { id: null, name: null, unit: null, amount: null, costs_by_grams: 0, costs_by_item: null },
            cost_by_item: 0,
            // configuration: {},
            unit_types: [],
            currency_types: [],
            system_isc_types: [],
            affectation_igv_types: [],
            categories: [],
            brands: [],
            accounts: [],
            show_has_igv: true,
            purchase_show_has_igv: true,
            supplies: 'black',
            suppliesFontSize: 20,
            recipeSub: null,
            recipeSubFontSize: null,
            have_account: false,
            columns_recipes: ["#", "Insumo o Sub-Rec", "Cantidad", "Unidad", "Costo", "Accion"],
            columns_cif: ["#", "Nombre", "Gasto Mensual", "Hors Diarias", "Hors Utiles", "Costo Total", "Accion"],
            search: {
                column: null,
                value: null
            },
            pagination: {},
            attribute_types: [],
            activeName: 'first',
            fromPharmacy: false,
            inventory_configuration: null,
            recordsSelect: [],
            recordsRecipes: [],
            recordsCIF: [],
            itemSelected: null,
            costs_total_item: null,
            spent_month: 0,
            hours_work_day: 0,
            hours_util_process: 0,
            records_items:[],
            totals_cif:0,
            totals_recipes_subrecipes:{total_grams:0,total_costs:0}
        }
    },
    async created() {
        this.loadConfiguration()
        if (this.pharmacy !== undefined && this.pharmacy == true) {
            this.fromPharmacy = true;
        }
        this.initForm();

        this.$eventHub.$on('reloadTables', () => {
            this.reloadTables()
        })
        this.getRecordsItems();
        this.getRecordsSelect('supplies');
        // await this.changeSupplies()

    },

    watch: {
        cost_by_item(value) {
            this.cost_by_item = value;
            this.calculateValueCostsByItem(value);
        },
        costs_total_item(value) {
            this.costs_total_item = value;
            this.calculateValueCostTotalByItem(value);
        },
        hours_util_process(value) {
            this.hours_util_process = value;
            this.calculateValueCostTotalByItem(value);
        },
        spent_month(value) {
            this.spent_month = value;
            this.calculateValueCostTotalByItem(value);
        },
        hours_work_day(value) {
            this.hours_work_day = value;
            this.calculateValueCostTotalByItem(value);
        }
    },

    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
        getRecordsItems() {
            this.loading_submit = true;
            return this.$http
                .get(`/items/records2?column=description&isPharmacy=false&type=PRODUCTS&value=`)
                .then(response => {
                    this.records_items = response.data.data.filter(e=>e.model != 'Insumos');
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
        },
        async changeSupplies() {
            this.supplies = 'black';
            this.suppliesFontSize = 20;
            this.recipeSub = null;
            this.recipeSubFontSize = 14;
            await this.getRecordsSelect('supplies');
        },
        getRecordsSelect(resource) {
            this.resource_temp = resource;
            this.loading_submit = true;
            return this.$http
                .get(`/${resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    if (resource == 'subrecipes') this.recordsSelect = this.groupSubRecipe(response.data.data);
                    else this.recordsSelect = response.data.data;
                })
                .catch(error => { })
                .then(() => {
                    this.loading_submit = false;
                });
        },
        groupSubRecipe(data) {
            let unit = 'Gramos';
            let data_parse = this.parseValues(data);
            
            for (let i = 0; i < data_parse.length; i++) {
                let costs_by_grams = 0, amount = 0;
                data_parse[i].subrecipes_supplies.map(e => {
                    costs_by_grams =  costs_by_grams + Number(e.costs_by_grams);
                    amount = amount + Number(e.quantity);
                    // amount = amount + (  e.unit == "Unidad" ? e.quantity * 1000: Number(e.quantity));
                });
                data_parse[i].cif.map(e => {
                    costs_by_grams =  costs_by_grams + Number(e.costs_total);
                    // amount = amount + (  e.unit == "Unidad" ? e.quantity * 1000: Number(e.quantity));
                });
                
                data_parse[i].unit = unit;
                data_parse[i].costs_by_grams = Number(( costs_by_grams/amount ).toFixed(5)) ;
            }
            return data_parse;
        },
        parseValues(data) {
            for (let i = 0; i < data.length; i++) {
                data[i].subrecipes_supplies = JSON.parse(data[i].subrecipes_supplies);
                data[i].cif = JSON.parse(data[i].cif);
                data[i].costs = JSON.parse(data[i].costs);
            }
            return data;
        },
        async changeSubRecipe() {
            this.recipeSub = 'black';
            this.recipeSubFontSize = 20;
            this.supplies = null;
            this.suppliesFontSize = 14;
            this.recordsSelect = [];
            this.resource_temp = 'subrecipes';
            await this.getRecordsSelect('subrecipes');
        },
        getQueryParameters() {
            if (this.productType == 'ZZ') {
                this.search.type = 'ZZ';
            }
            if (this.productType == 'PRODUCTS') {
                // Debe listar solo productos
                this.search.type = this.productType;
            }
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                isPharmacy: this.fromPharmacy,
                ...this.search
            });
        },

        purchaseChangeIsc() {
            if (!this.form.purchase_has_isc) {
                this.form.purchase_system_isc_type_id = null
                this.form.purchase_percentage_isc = 0
            }
        },
        changeIsc() {
            if (!this.form.has_isc) {
                this.form.system_isc_type_id = null
                this.form.percentage_isc = 0
            }
        },
        clickAddAttribute() {
            this.form.attributes.push({
                attribute_type_id: null,
                description: null,
                value: null,
                start_date: null,
                end_date: null,
                duration: null,
            })
        },
        async reloadTables() {
            await this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.unit_types = response.data.unit_types
                    this.accounts = response.data.accounts
                    this.currency_types = response.data.currency_types
                    this.system_isc_types = response.data.system_isc_types
                    this.affectation_igv_types = response.data.affectation_igv_types
                    this.warehouses = response.data.warehouses
                    this.categories = response.data.categories
                    this.brands = response.data.brands

                    this.form.sale_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
                    this.form.purchase_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
                })
        },
        initForm() {
            this.loading_submit = false,
            this.errors = {}
            this.form = {};
            this.recordsCIF = [];
            this.recordsRecipes = [];
            this.itemSelected=null
            this.form_costs={}
            this.form_recipes_subrecipes= { id: null, name: {description:null,id:null}, sale_price: 0 }

        },
        onSuccess(response, file, fileList) {
            if (response.success) {
                this.form.image = response.data.filename
                this.form.image_url = response.data.temp_image
                this.form.temp_path = response.data.temp_path
            } else {
                this.$message.error(response.message)
            }
        },
        
        resetForm() {
            this.initForm()
        },

        async create() {
            this.activeName = 'first'
            if (this.type) {
                if (this.type !== 'PRODUCTS') {
                    this.form.unit_type_id = 'ZZ';
                }
            }
            if (this.recordId) {
                if(this.recipeId != 'duplicate'){
                    this.titleDialog = "Editar " + (this.recipeId == 'recipe' ? 'Receta' : 'Sub-Receta');
                }else{
                    this.titleDialog = "Duplicar Receta";
                }
            } else {
                this.resetForm();
                this.titleDialog = this.recipeId == "recipe" ? 'Nueva Receta' : 'Nueva Sub-Receta';
            }
            if (this.recordId && this.recipeId != 'duplicate') {
                await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        let resp = response.data.data;
                        resp.subrecipes_supplies = JSON.parse(resp.subrecipes_supplies);
                        resp.cif = JSON.parse(resp.cif);
                        resp.costs = JSON.parse(resp.costs);
                        this.form_recipes_subrecipes = { id: resp.id, name: {description:resp.name,id:resp.item_id}, sale_price: resp.sale_price }
                        this.recordsRecipes = resp.subrecipes_supplies;
                        this.form_costs = resp.costs;
                        this.recordsCIF = resp.cif;
                        this.recipeId = resp.type_doc;
                        this.getTotalsRecords(this.recordsRecipes);
                        this.getTotalCifs(this.recordsCIF);

                        // this.calculatePriceItem();
                    })

            }


        },
        calculatePercentageOfProfitByPurchase() {
            if (this.form.percentage_of_profit === '') {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit) this.form.sale_unit_price = (this.form.purchase_unit_price * (100 + parseFloat(this.form.percentage_of_profit))) / 100
        },
        calculateValueCostsByItem(value) {
            this.form.amount = value;
            if (this.resource_temp == 'supplies') this.form.costs_by_item = ((this.itemSelected.unit == 'Kilogramo' ? this.itemSelected.costs_unit / 1000 : this.itemSelected.costs_unit) * value).toFixed(5);
            else if (this.resource_temp == 'subrecipes') this.form.costs_by_item = (this.itemSelected.costs_by_grams * value).toFixed(5);
        },
        calculateValueCostTotalByItem() {
            this.form_cif.spent_month = this.spent_month;
            this.form_cif.costs_total_item = this.costs_total_item;
            this.form_cif.hours_work_day = this.hours_work_day;
            this.form_cif.hours_util_process = this.hours_util_process;

            this.form_cif.costs_total_item = Number(((this.spent_month / 26) / this.hours_work_day * this.hours_util_process).toFixed(5));
        },
        addRecipes() {
            if(!this.form.costs_by_item) return this.$message.error('Agregar valores correctos')
            let record={ type: this.resource_temp, unit:"Gramos", quantity: this.form.amount, costs_by_grams: this.form.costs_by_item, id: null, name: this.itemSelected.name }
            // let record={ type: this.resource_temp, unit: this.itemSelected.unit, quantity: this.form.amount, costs_by_grams: this.form.costs_by_item, id: null, name: this.itemSelected.name }
            if(this.resource_temp == 'supplies' ) record.id_supplie= this.itemSelected.id
            if(this.resource_temp != 'supplies' ) record.id_subrecipe= this.itemSelected.id
            this.recordsRecipes.push(record);
            let sum_recipes = this.calculateCostsUnitProductRecipes(this.recordsRecipes);
            let sum_cif = this.calculateCostsUnitProductCif(this.recordsCIF);
            this.form_costs.costs_unit_product = Number((sum_recipes + sum_cif).toFixed(5));
            this.form_costs.margin_costs_soles = Number((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product).toFixed(5));
            this.form_costs.margin_costs_procentage = Number(((((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product) / this.form_recipes_subrecipes.sale_price) * 100)).toFixed(5));
            this.resetRecipeForm();
            this.getTotalsRecords(this.recordsRecipes);
        },
        getTotalsRecords(items){
            this.totals_recipes_subrecipes.total_grams =0
            this.totals_recipes_subrecipes.total_costs =0
            for (let i = 0; i < items.length; i++) {
                this.totals_recipes_subrecipes.total_grams =this.totals_recipes_subrecipes.total_grams + Number(items[i].quantity)
                this.totals_recipes_subrecipes.total_costs = this.totals_recipes_subrecipes.total_costs + Number(items[i].costs_by_grams) 
            }
            this.totals_recipes_subrecipes.total_grams = Number(this.totals_recipes_subrecipes.total_grams.toFixed(5))
            this.totals_recipes_subrecipes.total_costs = Number(this.totals_recipes_subrecipes.total_costs.toFixed(5))
        },
        calculateCostsUnitProductRecipes(recipes) {
            let sum = 0;
            for (let i = 0; i < recipes.length; i++) {
                sum = sum + Number(recipes[i].costs_by_grams);
            }
            return sum;
        },
        calculateCostsUnitProductCif(cifs) {
            let sum_cifs = 0;
            for (let j = 0; j < cifs.length; j++) {
                sum_cifs =sum_cifs + Number(cifs[j].costs_total);
            }
            return sum_cifs;
        },
        deleteRecipes(id) {
            console.log("index ",id);
            this.recordsRecipes.splice(Number(id), 1);
            let sum_recipes = this.calculateCostsUnitProductRecipes(this.recordsRecipes);
            let sum_cif = this.calculateCostsUnitProductCif(this.recordsCIF);
            this.form_costs.costs_unit_product = Number((sum_recipes + sum_cif).toFixed(5));
            this.form_costs.margin_costs_soles = Number((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product).toFixed(5));
            this.form_costs.margin_costs_procentage = Number(((((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product) / this.form_recipes_subrecipes.sale_price) * 100)).toFixed(5));
            this.getTotalsRecords(this.recordsRecipes);
        },
        deleteCif(id) {
            // let index = this.recordsCIF.findIndex(e => e.id == id)
            // console.log("index ", index);
            this.recordsCIF.splice(Number(id), 1);
            let sum_recipes = this.calculateCostsUnitProductRecipes(this.recordsRecipes);
            let sum_cif = this.calculateCostsUnitProductCif(this.recordsCIF);
            this.form_costs.costs_unit_product = Number((sum_recipes + sum_cif).toFixed(5));
            this.form_costs.margin_costs_soles = Number((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product).toFixed(5));
            this.form_costs.margin_costs_procentage = Number(((((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product) / this.form_recipes_subrecipes.sale_price) * 100)).toFixed(5));
            this.getTotalCifs(this.recordsCIF);
        },
        getValueCostByGrams() {
            let value = 0;
            // if (this.resource_temp == 'supplies') value = this.itemSelected && this.itemSelected.unit == 'Unidad' ? this.itemSelected.costs_unit / 1000 : this.itemSelected ? this.itemSelected.costs_unit : 0;
            // else if (this.resource_temp == 'subrecipes') value = this.itemSelected ? this.itemSelected.costs_by_grams : 0;
            if (this.resource_temp == 'supplies') value = this.itemSelected && this.itemSelected.unit == 'Kilogramo' ? this.itemSelected.costs_unit / 1000 : this.itemSelected ? this.itemSelected.costs_unit : 0;
            else if (this.resource_temp == 'subrecipes') value = this.itemSelected ? this.itemSelected.costs_by_grams : 0;
            return value;
        },
        resetRecipeForm() {
            this.form.id = null;
            this.form.name = null;
            this.form.unit = null;
            this.form.amount = 0;
            this.form.costs_by_grams = 0;
            this.form.costs_by_item = 0;
            this.itemSelected = {};
            this.cost_by_item = 0;
        },
        addCIF() {
            if(!this.form_cif.costs_total_item) return this.$message.error('Agregar valores correctos')
            this.recordsCIF.push({
                id: null,
                name: this.form_cif.name,
                spent_month: this.spent_month,
                hours_work_day: this.hours_work_day,
                hours_util_process: this.hours_util_process,
                costs_total: this.form_cif.costs_total_item
            });
            let sum_recipes = this.calculateCostsUnitProductRecipes(this.recordsRecipes);
            let sum_cif = this.calculateCostsUnitProductCif(this.recordsCIF);
            this.form_costs.costs_unit_product = Number((sum_recipes + sum_cif).toFixed(5));
            this.form_costs.margin_costs_soles = Number((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product).toFixed(5));
            this.form_costs.margin_costs_procentage = Number(((((this.form_recipes_subrecipes.sale_price - this.form_costs.costs_unit_product) / this.form_recipes_subrecipes.sale_price) * 100)).toFixed(5));
            this.resetCifForm();
            this.getTotalCifs(this.recordsCIF);
        },
        getTotalCifs(items){
            this.totals_cif =0 
            for (let i = 0; i < items.length; i++) {
                this.totals_cif = this.totals_cif + Number(items[i].costs_total)
            }
            this.totals_cif = Number(this.totals_cif.toFixed(5))
        },
        resetCifForm() {
            this.form_cif.name = null;
            this.spent_month = 0;
            this.hours_work_day = 0;
            this.form_cif.costs_total_item = 0;
            this.hours_util_process = 0;
        },
        async submit() {
            this.loading_submit = true;
            if (this.recordsRecipes.length == 0) this.$message.error('Selecione algunos insumos')
            if (this.recordsCIF.length == 0) this.$message.error('Llenar Valores en CIF')
            this.form_recipes_subrecipes.sale_price = Number(this.form_recipes_subrecipes.sale_price.toFixed(5))
            console.log("skjdf ",this.form_recipes_subrecipes);
            await this.$http.post(`/${this.resource}`, { costs: this.form_costs, recipe_subrecipe: this.form_recipes_subrecipes, recipes_details: this.recordsRecipes, cif: this.recordsCIF, type_doc: this.recipeId })
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
        close() {
            this.$emit('update:showDialog', false)
            this.resetForm()
        },
        calculatePriceItem(){
            let resp =  0;
            this.form_recipes_subrecipes.name.description = this.records_items.find(e=>e.id == this.form_recipes_subrecipes.name.id);
            if(this.form_recipes_subrecipes.name.description){
                    if( this.form_recipes_subrecipes.name.description.has_igv == true) resp = this.form_recipes_subrecipes.name.description.amount_sale_unit_price
                    else resp = this.form_recipes_subrecipes.name.description.amount_sale_unit_price + ( (this.form_recipes_subrecipes.name.description.amount_sale_unit_price / 1.18) * 0.18)
                }
            this.form_recipes_subrecipes.sale_price = Number(resp.toFixed(5));
            return resp ;
        },
        async duplicar(){
            if(!this.form_recipes_subrecipes.name.description) this.$message.error('Selecione el producto.')
            await this.$http.post(`/${this.resource}/duplicated`, { id_to_duplicate:this.recordId,id:this.form_recipes_subrecipes.name.id })
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
        },
        async searchRemoteItems(input) {
            if (input.length > 2) {
                this.loading_search = true
                const params = {
                    'value': input,
                    'by':"description",
                    'search_by_barcode': this.search_item_by_barcode ? 1 : 0
                }
                await this.$http.get(`/${this.resource}/search-items/`, {params})
                    .then(response => {
                        this.records_items = response.data.items
                        this.loading_search = false
                        
                    })
            } else {
                await this.filterItems()
            }

        },
        focusSelectItem() {
            this.$refs.selectSearchNormal.$el.getElementsByTagName('input')[0].focus()
        },
    }
}
</script>

<template>
    <el-dialog :close-on-click-modal="false"
               :title="titleDialog"
               :visible="showDialog"
               append-to-body
               class="pt-0"
               top="7vh"
               width="65%"
               @close="close"
               @open="create">
        <form autocomplete="off"
              @submit.prevent="submit">


            <el-tabs v-model="activeName">
                <el-tab-pane class
                             name="first">
                    <span slot="label">General</span>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.name}"
                                 class="form-group">
                                <label class="control-label">Nombre<span class="text-danger">*</span></label>
                                <el-input v-model="form.name"
                                          dusk="description"></el-input>
                                <small v-if="errors.description"
                                       class="form-control-feedback"
                                       v-text="errors.description[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.second_name}"
                                 class="form-group">
                                <label class="control-label">Nombre secundario</label>
                                <el-input v-model="form.second_name"
                                          dusk="second_name"></el-input>
                                <small v-if="errors.second_name"
                                       class="form-control-feedback"
                                       v-text="errors.second_name[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.costs_unit}"
                                 class="form-group">
                                <label class="control-price_unit">Costo Unitario <span class="text-danger">*</span></label>
                                <el-input v-model="form.costs_unit"
                                          dusk="costs_unit"></el-input>
                                <small v-if="errors.costs_unit"
                                       class="form-control-feedback"
                                       v-text="errors.costs_unit[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.unit}"
                                 class="form-group">
                                <label class="control-label">Unidad<span class="text-danger">*</span></label>
                                <el-select v-model="form.unit"
                                           dusk="unit_type_id">
                                    <el-option v-for="option in unit_types"
                                               :key="option.id"
                                               :label="option.description"
                                               :value="option.id"></el-option>
                                </el-select>
                                <small v-if="errors.unit_type_id"
                                       class="form-control-feedback"
                                       v-text="errors.unit_type_id[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.description}"
                                 class="form-group">
                                 <label class="control-label">Categoria <span class="text-danger">*</span></label>
                                 <a href="#" @click.prevent="showdalognewcat = true">[+ Nuevo]</a>
                                <el-select v-model="form.category_supplies" dusk="unit_type_id">
                                    <el-option v-for="option in category_supplies"
                                               :key="option.id"
                                               :label="option.name"
                                               :value="option.id"></el-option>
                                </el-select>
                            </div>
                        </div>

                    </div>
                </el-tab-pane>

            </el-tabs>
            <div class="form-actions text-right pt-2 mt-2">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit"
                           native-type="submit"
                           type="primary">Guardar
                </el-button>
            </div>
        </form>

        <lots-form
            :lots="form.lots"
            :recordId="recordId"
            :showDialog.sync="showDialogLots"
            :stock="form.stock"
            @addRowLot="addRowLot">
        </lots-form>
        <el-dialog 
            :visible="showdalognewcat"
            :title="titleDialogCat"
            @close="closeNewCat"
            >
            <el-tabs v-model="activeName">
                <el-tab-pane class
                             name="first">
                    <span slot="label">Nuevo</span>
                    <div class="row">
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.description}"
                                    class="form-group">
                                    <label class="control-label">Nombre <span class="text-danger">*</span></label>
                                    <el-input v-model="form2.name" dusk="description"></el-input>
                            </div>
                        </div>

                        <div class="form-actions text-right pt-2 mt-2">
                            <el-button :loading="loading_submit"
                                native-type="submit"
                                @click.prevent="saveCategoryNew()"
                                type="primary">Guardar
                            </el-button>
                        </div>
                    </div>
                </el-tab-pane>

            </el-tabs>
        </el-dialog>
    </el-dialog>

</template>

<script>
import LotsForm from './partials/lots.vue'
import ExtraInfo from './partials/extra_info'
import {mapActions, mapState} from "vuex";
import {ItemOptionDescription, ItemSlotTooltip} from "../../../helpers/modal_item";


export default {
    props: [
        'showDialog',
        'recordId',
        'external',
        'type',
        'pharmacy',
    ],
    components: {
        LotsForm,
        ExtraInfo
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
        canSeeProduction:function(){
            if(this.config && this.config.production_app) return this.config.production_app
            return false;
        },
        requireSupply:function(){

            if(this.form.is_for_production) {

                if( this.form.is_for_production == true) return true
            };
            return false;
        },

        canShowExtraData: function () {
            if (this.config && this.config.show_extra_info_to_item !== undefined) {
                return this.config.show_extra_info_to_item;
            }
            return false;
        },
        showPharmaElement() {

            if (this.fromPharmacy === true) return true;
            if (this.config.is_pharmacy === true) return true;
            return false;
        },
        showPointSystem()
        {
            if(this.config) return this.config.enabled_point_system

            return false
        }

    },

    data() {
        return {
            loading_search: false,
            showDialogLots: false,
            form_category: {add: false, name: null, id: null},
            form_brand: {add: false, name: null, id: null},
            warehouses: [],
            items: [],
            loading_submit: false,
            showPercentagePerception: false,
            has_percentage_perception: false,
            percentage_perception: null,
            enabled_percentage_of_profit: false,
            titleDialog: null,
            resource: 'supplies',
            errors: {},
            item_suplly: {},
            headers: headers_token,
            form: {
                item_supplies:[],
                is_for_production:false,
            },
            // configuration: {},
            unit_types: [{id:'NIU',description:'Unidad'},{id:'GRM',description:'Kilogramo'}],

            category_supplies: [],
            system_isc_types: [],
            affectation_igv_types: [],
            categories: [],
            brands: [],
            accounts: [],
            show_has_igv: true,
            purchase_show_has_igv: true,
            have_account: false,
            attribute_types: [],
            activeName: 'first',
            fromPharmacy: false,
            inventory_configuration: null,
            showdalognewcat:false,
            titleDialogCat:"Agregar Nueva Categoria",
            form2:{}
        }
    },

    async created() {
        if (this.pharmacy !== undefined && this.pharmacy == true) {
            this.fromPharmacy = true;
        }
        await this.initForm();

        await this.$http.get(`/${this.resource}/tables`)
            .then(response => {
                let data = response.data;
                this.category_supplies = data.data
            })

        this.$eventHub.$on('submitPercentagePerception', (data) => {
            this.form.percentage_perception = data
            if (!this.form.percentage_perception) this.has_percentage_perception = false
        })

        this.$eventHub.$on('reloadTables', () => {
            this.reloadTables()
        })

        await this.setDefaultConfiguration()

    },

    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
        setDefaultConfiguration() {

        },
        async getCategories(){
            await this.$http.get(`/${this.resource}/tables`)
            .then(response => {
                let data = response.data;
                this.category_supplies = data.data
            })
        },
        async saveCategoryNew(){
            await this.$http.post(`/${this.resource}/savecat`, this.form2)
                .then(response => {
                    console.log(response.data)
                    if (response.data.success) {
                        this.$message.success(response.data.data.message)
                        // this.close()
                        this.showdalognewcat = false;
                        this.getCategories();
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                        this.$message.error(error.response.data.message)
                    }
                })
                .then(() => {
                    this.loading_submit = false
                })
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

                    this.form.purchase_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
                })
        },
        changeLotsEnabled() {

            // if(!this.form.lots_enabled){
            //     this.form.lot_code = null
            //     this.form.lots = []
            // }

        },
        changeProductioTab(){

        },
        addRowLot(lots) {
            this.form.lots = lots
        },
        clickLotcode() {
            this.showDialogLots = true
        },
        changeHaveAccount() {
            if (!this.have_account) this.form.account_id = null
        },
        changeEnabledPercentageOfProfit() {
            // if(!this.enabled_percentage_of_profit) this.form.percentage_of_profit = 0
        },
        clickDelete(id) {

            this.$http.delete(`/${this.resource}/item-unit-type/${id}`)
                .then(res => {
                    if (res.data.success) {
                        this.loadRecord()
                        this.$message.success('Se eliminó correctamente el registro')
                    }
                })
                .catch(error => {
                    if (error.response.status === 500) {
                        this.$message.error('Error al intentar eliminar');
                    } else {
                        console.log(error.response.data.message)
                    }
                })

        },
        changeHasPerception() {
            if (!this.form.has_perception) {
                this.form.percentage_perception = null
            }
        },
        clickAddRow() {
            this.form.item_unit_types.push({
                id: null,
                description: null,
                unit_type_id: 'NIU',
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,
                barcode: null
            })
        },
        clickCancel(index) {
            this.form.item_unit_types.splice(index, 1)
        },
        initForm() {
            this.loading_submit = false,
            this.errors = {}

            this.form = {
                id: null,
                name: null,
                second_name: null,
                costs_unit: 0,
                unit: null,
                category_supplies:null,

            }
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
            this.form.purchase_affectation_igv_type_id = (this.affectation_igv_types.length > 0) ? this.affectation_igv_types[0].id : null
            this.setDefaultConfiguration()
        },
        async create() {
            // console.log(this.warehouses)
            // this.warehouses = this.warehouses.map(w => {
            //     delete w.price;
            //     return w;
            // });
            this.activeName =  'first'
            if (this.type) {
                if (this.type !== 'PRODUCTS') {
                    this.form.unit_type_id = 'ZZ';
                }
            }
            this.titleDialog = (this.recordId) ? 'Editar Insumo' : 'Nuevo Insumo'

            if (this.recordId) {
                await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        this.has_percentage_perception = (this.form.percentage_perception) ? true : false
                        // let warehousePrices = response.data.data.warehouse_prices;
                        // console.error(warehousePrices);
                        // if (warehousePrices.length > 0) {
                        //     this.warehouses = this.warehouses.map(w => {
                        //         let price = warehousePrices.find(wp => wp.warehouse_id === w.id);
                        //         if (price) {
                        //             var priceToJson = {...price};
                        //             w.price = priceToJson.price;
                        //         }
                        //         return w;
                        //     });
                        // } else {
                        //     this.warehouses = this.warehouses.map(w => {
                        //         delete w.price;
                        //         return w;
                        //     });
                        // }
                    })

            }

            // this.setDataToItemWarehousePrices()

        },
        setDataToItemWarehousePrices() {

            this.warehouses.forEach(warehouse => {

                let item_warehouse_price = _.find(this.form.item_warehouse_prices, {warehouse_id: warehouse.id})

                if (!item_warehouse_price) {

                    this.form.item_warehouse_prices.push({
                        id: null,
                        item_id: null,
                        warehouse_id: warehouse.id,
                        price: null,
                        description: warehouse.description,
                    })
                }

            });

            this.form.item_warehouse_prices = _.orderBy(this.form.item_warehouse_prices, ['warehouse_id'])

        },
        loadRecord() {
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        console.error(this.form.is_for_production)
                    })
            }
        },

        calculatePercentageOfProfitByPurchase() {
            if (this.form.percentage_of_profit === '') {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit) this.form.sale_unit_price = (this.form.purchase_unit_price * (100 + parseFloat(this.form.percentage_of_profit))) / 100
        },
        calculatePercentageOfProfitByPercentage() {
            if (this.form.percentage_of_profit === '') {
                this.form.percentage_of_profit = 0;
            }

            if (this.enabled_percentage_of_profit) this.form.sale_unit_price = (this.form.purchase_unit_price * (100 + parseFloat(this.form.percentage_of_profit))) / 100
        },
        validateItemUnitTypes() {

            let error_by_item = 0

            if (this.form.item_unit_types.length > 0) {

                this.form.item_unit_types.forEach(item => {

                    if (parseFloat(item.quantity_unit) < 0.0001) {
                        error_by_item++
                    }

                })

            }

            return error_by_item

        },
        onlyNumbers(costs_unit){
            let pattern = /^[0-9]+([,.][0-9]+)?$/;
            return costs_unit.match(pattern) ? true : false;
        },
        async submit() {
            if(!this.form.name) return this.$message.error('Ingrese el nombre del insumo');
            if(!this.form.unit) return this.$message.error('Elija una Unidad de Medida');
            if(!this.form.category_supplies) return this.$message.error('Elija una Categoria de Insumos');
            if(!this.onlyNumbers(this.form.costs_unit+''))  return this.$message.error('Precio Unitario debe ser numero');
            this.loading_submit = true
            console.log("ajbf",this.form);
            this.form.category_supplies = Number(this.form.category_supplies)
            await this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    console.log(response.data)
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
                        console.log(error)
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
        closeNewCat() {
            this.showdalognewcat = false;
            this.form2.name = "";
        },
        changeHasIsc() {
            this.form.system_isc_type_id = null
            this.form.percentage_isc = 0
            this.form.suggested_price = 0
        },
        changeSystemIscType() {
            if (this.form.system_isc_type_id !== '03') {
                this.form.suggested_price = 0
            }
        },
        saveCategory() {
            this.form_category.add = false

            this.$http.post(`/categories`, this.form_category)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.categories.push(response.data.data)
                        this.form_category.name = null
                    } else {
                        this.$message.error('No se guardaron los cambios')
                    }
                })
                .catch(error => {

                })
        },
        saveBrand() {
            this.form_brand.add = false

            this.$http.post(`/brands`, this.form_brand)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.brands.push(response.data.data)
                        this.form_brand.name = null

                    } else {
                        this.$message.error('No se guardaron los cambios')
                    }
                })
                .catch(error => {

                })


        },
        changeAttributeType(index) {
            let attribute_type_id = this.form.attributes[index].attribute_type_id
            let attribute_type = _.find(this.attribute_types, {id: attribute_type_id})
            this.form.attributes[index].description = attribute_type.description
        },
        clickRemoveAttribute(index) {
            this.form.attributes.splice(index, 1)
        },
        async searchRemoteItems(input) {
            if (input.length > 2) {
                this.loading_search = true
                const params = {
                    'input': input,
                    'search_by_barcode': this.search_item_by_barcode ? 1 : 0,
                    'production':1
                }
                await this.$http.get(`/${this.resource}/search-items/`, {params})
                    .then(response => {
                        this.items = response.data.items
                        this.loading_search = false
                        // this.enabledSearchItemsBarcode()
                        // this.enabledSearchItemBySeries()
                        if (this.items.length == 0) {
                            // this.filterItems()
                        }
                    })
            } else {
                // await this.filterItems()
            }

        },
        getItems() {
            this.$http.get(`/${this.resource}/item/tables`).then(response => {
                this.items = response.data.items
            })
        },
        changeItem() {
            this.getItems();
            this.item_suplly = _.find(this.items, {'id': this.item_suplly});
            /*
            this.form.unit_price = this.item_suplly.sale_unit_price;

            this.lots = this.item_suplly.lots

            this.form.has_igv = this.item_suplly.has_igv;

            this.form.affectation_igv_type_id = this.item_suplly.sale_affectation_igv_type_id;
            this.form.quantity = 1;
            this.item_unit_types = this.item_suplly.item_unit_types;

            (this.item_unit_types.length > 0) ? this.has_list_prices = true : this.has_list_prices = false;
            */

        },
        focusSelectItem() {
            this.$refs.selectSearchNormal.$el.getElementsByTagName('input')[0].focus()
        },

        ItemSlotTooltipView(item) {
            return ItemSlotTooltip(item);
        },
        ItemOptionDescriptionView(item) {
            return ItemOptionDescription(item)
        },
        clickAddSupply(){
            // item_supplies
            if(this.form.supplies === undefined) this.form.supplies = [];
            let item = this.item_suplly;
            if(item === null) return false;
            if(item === undefined) return false;
            if(item.id=== undefined) return false;
            this.items = [];
            this.item_suplly = {}

            item.item_id = this.form.id
            //item.individual_item_id = item.id
            item.individual_item_id = item.id
            item.individual_item = {
                'description':item.description
            }
            //item.individual_item = item
            // item.quantity = 0
            //if(isNaN(item.quantity)) item.quantity = 0 ;
            this.form.supplies.push(item)
            this.changeItem()


        },
    }
}
</script>

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
                                <label class="control-label">Nombre:<span class="text-danger">*</span></label>
                                <el-input v-model="form.name"
                                          dusk="name"></el-input>
                                <small v-if="errors.name"
                                       class="form-control-feedback"
                                       v-text="errors.name[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div :class="{'has-danger': errors.sale_price}"
                                 class="form-group">
                                <label class="control-label">Precio de Venta:<span class="text-danger">*</span></label>
                                <el-input v-model="form.sale_price"
                                          dusk="sale_price"></el-input>
                                <small v-if="errors.sale_price"
                                       class="form-control-feedback"
                                       v-text="errors.sale_price[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.owner}"
                                 class="form-group">
                                <label class="control-label">Dueño del Pedido:<span class="text-danger">*</span></label>
                                <el-input v-model="form.owner"
                                          dusk="owner"></el-input>
                                <small v-if="errors.owner"
                                       class="form-control-feedback"
                                       v-text="errors.owner[0]"></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div :class="{'has-danger': errors.delivery_date}"
                                 class="form-group">
                                <label class="control-label">Fecha de la Entrega: <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                            <el-date-picker v-model="form.delivery_date" type="date"
                                                value-format="yyyy-MM-dd" format="dd/MM/yyyy"
                                                :clearable="false">
                                            </el-date-picker>
                                        </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="control-label">Hora de la Entrega: <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <input v-model="form.hour" type="time" id="appt" name="appt" min="09:00" max="23:59" required>
                                </div>
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

    </el-dialog>
</template>

<script>
import LotsForm from './partials/lots.vue';
import ExtraInfo from './partials/extra_info';
import {mapActions, mapState} from "vuex";
import {ItemOptionDescription, ItemSlotTooltip} from "../../../helpers/modal_item";
import moment from 'moment';


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
            resource: 'orderschedule',
            errors: {},
            item_suplly: {},
            headers: headers_token,
            form: {id:null, name:"Prueba",sale_price:123.24,owner:"Dueño 1",delivery_date:moment().format("YYYY-MM-DD") } ,
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
            have_account: false,
            item_unit_type: {
                id: null,
                unit_type_id: null,
                quantity_unit: 0,
                price1: 0,
                price2: 0,
                price3: 0,
                price_default: 2,

            },
            attribute_types: [],
            activeName: 'first',
            fromPharmacy: false,
            inventory_configuration: null
        }
    },
    async created() {
        this.loadConfiguration()
        if (this.pharmacy !== undefined && this.pharmacy == true) {
            this.fromPharmacy = true;
        }
        await this.initForm();
        this.$eventHub.$on('submitPercentagePerception', (data) => {
            this.form.percentage_perception = data
            if (!this.form.percentage_perception) this.has_percentage_perception = false
        })

        this.$eventHub.$on('reloadTables', () => {
        })


    },

    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
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
            this.form = { name:"Prueba", sale_price:123.24, owner:"Dueño 1" , delivery_date:"" } ;
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
        changeAffectationIgvType() {

            let affectation_igv_type_exonerated = [20, 21, 30, 31, 32, 33, 34, 35, 36, 37]
            let is_exonerated = affectation_igv_type_exonerated.includes((parseInt(this.form.sale_affectation_igv_type_id)));

            if (is_exonerated) {
                this.show_has_igv = false
                this.form.has_igv = true
            } else {
                this.show_has_igv = true
            }

        },
        
        resetForm() {
            this.form ={}
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
            this.titleDialog = (this.recordId) ? 'Editar Orden' : 'Nuevo Orden'

            if (this.recordId) {
                await this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        this.has_percentage_perception = (this.form.percentage_perception) ? true : false
                        this.changeAffectationIgvType()
                        this.changePurchaseAffectationIgvType()
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


        },
        loadRecord() {
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                        console.error(this.form.is_for_production)
                        this.changeAffectationIgvType()
                        this.changePurchaseAffectationIgvType()
                    })
            }
        },
        calculatePercentageOfProfitBySale() {
            let difference = parseFloat(this.form.sale_unit_price) - parseFloat(this.form.purchase_unit_price);

            if (parseFloat(this.form.purchase_unit_price) === 0) {
                this.form.percentage_of_profit = 0;
            } else {
                if (this.enabled_percentage_of_profit) this.form.percentage_of_profit = difference / parseFloat(this.form.purchase_unit_price) * 100;
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
        async submit() {

            this.loading_submit = true
            this.form.id=null;
            this.form.delivery_date = this.form.delivery_date + " "+this.form.hour + "00";
            await this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    console.log(response.data)
                    if (response.data.success) {
                        this.$message.success(response.data.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    } else {
                        this.$message.error(response.data.data.message)
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

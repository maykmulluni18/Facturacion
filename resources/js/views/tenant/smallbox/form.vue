<template>
    <el-dialog :close-on-click-modal="false" :title="titleDialog" :visible="showDialog" append-to-body class="pt-0"
        top="7vh" width="35%" @close="close" @open="create">
        <form autocomplete="off" @submit.prevent="submit">


            <div class="container px-lg-5">
                <div class="row mx-lg-n5">
                    <div class="col py-3 px-lg-5 border bg-light" type="button" v-bind:style="{color:gasto_,fontSize:gastoFontSize + 'px'}" @click.prevent="changeGasto()">GASTO</div>
                    <div class="col py-3 px-lg-5 border bg-light" type="button" v-bind:style="{color:ingreso_,fontSize:ingresoFontSize + 'px'}" @click.prevent="changeIngreso()">INGRESO</div>
                </div>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-md-6"><strong>Fecha del Movimiento</strong></div>
                    <el-date-picker v-model="form.date_movement" type="date" value-format="yyyy-MM-dd"  ></el-date-picker>
                    <div class="col-md-6"><Strong>Descripcion del Movimiento</Strong></div>
                    <input type="text" class="form-control" v-model="form.description_movement" id="exampleInputDescriptionMovement" placeholder="Descripcion movimiento">
                    <div class="col-md-6"><strong>Monto</strong></div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">S/</span>
                        </div>
                        <input type="text" class="form-control" v-model="form.amount_movement" aria-label="Amount (to the nearest dollar)">
                    </div>
                </div>
            </div>
            <div class="form-actions text-right pt-2 mt-2">
                <el-button @click.prevent="close()">Cancelar</el-button>
                <el-button :loading="loading_submit" native-type="submit" type="primary">Registrar Movimiento
                </el-button>
            </div>
        </form>

    </el-dialog>
</template>

<script>
import LotsForm from './partials/lots.vue'
import ExtraInfo from './partials/extra_info'
import { mapActions, mapState } from "vuex";
import { ItemOptionDescription, ItemSlotTooltip } from "../../../helpers/modal_item";
import color from "../../../../../modules/Inventory/Resources/assets/js/extra_info/color/index.vue";


export default {
    props: [
        'showDialog',
        'recordId',
        'external',
        'type',
        'pharmacy',
        'saldoTotal'
    ],
    components: {
        LotsForm,
        ExtraInfo
    },
    computed: {
        color() {
            return color
        },

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
        showPointSystem() {
            if (this.config) return this.config.enabled_point_system

            return false
        }

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
            resource: 'smallbox',
            errors: {},
            item_suplly: {},
            headers: headers_token,
            form: {id:null},
            gasto_:null,
            gastoFontSize:null,
            ingreso_:null,
            ingresoFontSize:null,
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

    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
        changeGasto(){
            this.gasto_ = '#1e88e5';
            this.gastoFontSize = 20;
            this.ingreso_ =null;
            this.ingresoFontSize =14;
        },
        changeIngreso(){
            this.ingreso_ = '#1e88e5';
            this.ingresoFontSize = 20;
            this.gasto_ = null;
            this.gastoFontSize=14;
        },

        clickLotcode() {
            this.showDialogLots = true
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


        initForm() {
            this.loading_submit = false;
            this.form = {
                id: null,
                decription_movement:null,
                type_movement:null,
                date_movement: null,
                amount_movement:0
            }
            this.gasto_=null;
            this.ingreso_=null;
            this.gastoFontSize=14;
            this.ingresoFontSize=14;
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
            // console.log(this.warehouses)
            // this.warehouses = this.warehouses.map(w => {
            //     delete w.price;
            //     return w;
            // });
            this.activeName = 'first'
            if (this.type) {
                if (this.type !== 'PRODUCTS') {
                    this.form.unit_type_id = 'ZZ';
                }
            }
            this.titleDialog = (this.recordId) ? 'Editar Movimiento' : 'Nuevo Registro ' + ' Saldo Disponible S/ '+ this.saldoTotal

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

            this.setDataToItemWarehousePrices()

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

        async submit() {

            this.form.type_movement = this.gasto_ ? 1 : 0; // 1 quiere decir gasto y 0 ingreso
            this.loading_submit = true

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





        getItems() {
            this.$http.get(`/${this.resource}/item/tables`).then(response => {
                this.items = response.data.items
            })
        },
        changeItem() {
            this.getItems();
            this.item_suplly = _.find(this.items, { 'id': this.item_suplly });
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

    }
}
</script>

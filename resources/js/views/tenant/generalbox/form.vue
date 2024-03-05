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
                    <input type="date" class="form-control" id="exampleInputDateMovement" v-model="form.date_movement" placeholder="">
                    <div class="col-md-6"><Strong>Descripcion del Movimiento</Strong></div>
                    <input type="text" class="form-control" v-model="form.description_movement" id="exampleInputDescriptionMovement"
                        placeholder="Descripcion movimiento">
                    <div class="col-md-6"><Strong>Categoria</Strong></div>
                    <select class="form-control form-control-sm" v-model="form.category_movement">
                        <option> Categoria 1</option>
                        <option> Categoria 2</option>
                        <option> Categoria 3</option>
                    </select>

                    <div class="col-md-6"><strong>Monto</strong></div>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">S/</span>
                        </div>
                        <input type="text" class="form-control" v-model="form.amount_movement" aria-label="Amount (to the nearest dollar)">
                    </div>

                    <div class="col-md-6"><Strong>Medios de gastos/ingreso:</Strong></div>
                    <select class="form-control form-control-sm" v-model="form.half_spent" >
                        <option> Medio 1</option>
                        <option> Medio 2</option>
                        <option> Medio 3</option>
                    </select>
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
import ExtraInfo from './partials/extra_info'
import { mapActions, mapState } from "vuex";
import { ItemOptionDescription, ItemSlotTooltip } from "../../../helpers/modal_item";


export default {
    props: [
        'showDialog',
        'recordId',
        'external',
        'type',
        'pharmacy',
    ],
    components: {
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
        canSeeProduction: function () {
            if (this.config && this.config.production_app) return this.config.production_app
            return false;
        },
        requireSupply: function () {

            if (this.form.is_for_production) {

                if (this.form.is_for_production == true) return true
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
            resource: 'generalbox',
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
            this.reloadTables()
        })

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
            this.loading_submit = false,
                this.errors = {}

            this.form = {
                id: null,
                description_movement:null,
                category_movement:null,
                half_spent:null,
                type_movement:null,
                date_movement:null,
                amount_movement:0
            }

            this.show_has_igv = true
            this.purchase_show_has_igv = true
            this.enabled_percentage_of_profit = false
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
            this.titleDialog = (this.recordId) ? 'Editar Movimiento' : 'Nuevo Movimiento ' + ' Saldo Disponible S/ 9345'

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

            console.log(this.form)
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

    }
}
</script>

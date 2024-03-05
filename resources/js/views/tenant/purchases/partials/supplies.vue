<template>
    <el-dialog :title="titleDialog"
               :visible="showDialog"
               @close="close">
        <div>
            <div class="row">
                <div class="col-lg-4">

                </div>
                <div class="form-group"> 
                    <label class="control-label">Unidad de Medida  <span class="text-danger"> {{ supplie_selected_unit ? supplie_selected_unit.unit == 'Unidad' ?"(u)":"(gr)" : "No se ha seleccionado un insumo" }} </span></label>
                    <br>
                    <label class="control-label">Selecciona el Insumo <span class="text-danger">*</span></label>
                    <el-select v-model="supplie_selected">
                        <el-option v-for="option in records_supplies"
                            :key="option.id"
                            :label="option.name"
                            :value="option.id" ></el-option>
                        </el-select>

                    <label class="control-label">Cantidad  <span class="text-danger">*</span></label>
                    <el-input v-model="supplie_selected_quantity"></el-input>
                    <label class="control-label">Costo  <span class="text-danger">*</span></label>
                    <el-input v-model="supplie_selected_costs"
                                :value="supplie_selected_costs"
                                :maxlength="4"
                    ></el-input>

                    <label class="control-label">Total {{ total ? total: 0 }} </label>

                </div>
                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="close()">Cancelar</el-button>
                    <el-button
                               native-type="submit"
                               type="primary" @click.prevent="submit()">Agregar
                    </el-button>
                </div>
                
            </div>
        </div>
        <item-form :external="true"
                   :showDialog.sync="showDialogNewItem"></item-form>

    </el-dialog>
</template>
<style>
.el-select-dropdown {
    margin-right: 5% !important;
    max-width: 80% !important;
}

.el-select-currency {
    width: 59px;
}

.input-with-select {
    background-color: #FFFFFF;
}
</style>
<script>

import itemForm from '../../items/form.vue'
import {calculateRowItem} from '../../../../helpers/functions'
import Keypress from "vue-keypress";
import {ItemOptionDescription, ItemSlotTooltip} from "../../../../helpers/modal_item";
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
import moment from "moment";

export default {
    props: [
        'showDialog',
        'currencyTypeIdActive',
    ],
    components: {itemForm, Keypress},
    computed: {
        ...mapState([
            'config',
            'hasGlobalIgv',
            'colors',
            'CatItemUnitsPerPackage',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemMoldCavity',
            'CatItemProductFamily',
            'CatItemSize',
            'extra_colors',
            'extra_CatItemUnitsPerPackage',
            'extra_CatItemMoldProperty',
            'extra_CatItemSize',
            'extra_CatItemUnitBusiness',
            'extra_CatItemStatus',
            'extra_CatItemPackageMeasurement',
            'extra_CatItemMoldCavity',
            'extra_CatItemProductFamily',
        ]),
        
        
    },
    data() {
        return {
            datEmision: {
                disabledDate(time) {
                    return time.getTime() < moment();
                }
            },
            titleDialog: 'Agregar Insumos',
            showDialogLots: false,
            resource: 'purchases',
            showDialogNewItem: false,
            errors: {},
            form: {},
            supplie_selected:null,
            supplie_selected_unit:null,
            supplie_selected_quantity:0,
            supplie_selected_costs:0,
            records_supplies:[],
            total:0,
        }
    },
    created() {
        this.activeName = 'first'
        this.$eventHub.$on('reloadDataItems', (item_id) => {
        })
        this.restartForm()
        this.getRecordsSupplies()
    },
    watch: {
        supplie_selected(value){
            this.setCostValue(value);
            this.setValueTotal(1, this.supplie_selected_costs)
        },
        supplie_selected_quantity(value) {
            this.supplie_selected_quantity = Number(value) ? Number(value):0;
            this.setValueTotal(Number(value),this.supplie_selected_costs);
        },
        supplie_selected_costs(value) {
            this.supplie_selected_costs = value;
            this.setValueTotal(this.supplie_selected_quantity,Number(value));
        }
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
            'loadHasGlobalIgv',
            'clearExtraInfoItem',
        ]),
        close() {
            this.initForm()
            this.$emit('update:showDialog', false)
        },
        initForm(){
            this.form = {}
        },
        setCostValue(item){
            this.supplie_selected_costs = item ? Number(this.records_supplies.find(e=>e.id == Number(item)).costs_unit) :  0
        },
        
        setValueTotal(quantity,costs){
            this.supplie_selected_unit = this.records_supplies.find(e=>e.id == this.supplie_selected )
            console.log("sdf ",this.supplie_selected_unit);
            this.total = !quantity || !costs ? 0: (Number(quantity) * Number(costs)).toFixed(2)
        },
        async submit(){
            let supplie = this.records_supplies.find(e=>e.id == this.supplie_selected)
            let new_price = (Number(this.supplie_selected_costs) / 1.18).toFixed(2);
            let supplie_add = {
                type_doc:"supplie",
                affectation_igv_type:{description:0},
                warehouse_description:"Almacén Oficina Principal",
                lot_code:null,
                total_discount:0,
                total_charge:0,
                // total:(Number(supplie.costs_unit) * Number(this.supplie_selected.amount)).toFixed(2),
                total:this.total,
                unit_value:Number(new_price) ,
                item: {description:supplie.name,unit_type_id:"gr",series_enabled:""},
                unit_price: Number(supplie.costs_unit),
                quantity:this.supplie_selected_quantity ,
                id:supplie.id
            } ;
            this.restartForm()
            this.$emit('add',supplie_add )
        },
        async getRecordsSupplies(){
            // await this.changePaymentMethodType(false)
            await this.$http.get(`/supplies/records`, this.form)
                .then(response => {
                    this.records_supplies = response.data.data
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
        restartForm(){
            this.supplie_selected = null
            this.supplie_selected_costs = 0
            this.supplie_selected_quantity = 0
        }
    }
}

</script>

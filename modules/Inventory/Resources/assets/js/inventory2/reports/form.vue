<template>
    <el-dialog :title="titleDialog"
               :visible="showDialog"
               @close="close">
        <div class="container">
            <div class="row">
                <div class="col">
                    <label class="control-label"> Sub Receta <span class="text-danger">*</span></label>
                    <select v-model="item_selected" class="form-control form-control-sm mb-1" >
                        <option v-for="option in records_supplies" :value="option"> {{ option.name + " - Cantidad Estándar " + option.quantity_current + " gr"}}</option>
                    </select>
                </div>
                
                <div class="col">
                    <label class="control-label">Cantidad  (gr) <span class="text-danger">*</span></label>
                    <el-input v-model="item_selected_amount" ></el-input>
                </div>
            </div>
            <div class="col">
                <el-button native-type="submit" type="primary" @click.prevent="addRegister()">Registrar
                </el-button>
            </div>
        </div>

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
import moment from "moment";
import queryString from "query-string";
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
export default {
    props: ['showDialog'],
    data() {
        return {
            loading_submit: false,
            // showDialogLots: false,
            // showDialogLotsOutput: false,
            // titleDialog: null,
            total_profit: 0,
            titleDialog: 'Registrar Sub Receta Inventario 2',
            total_all_profit: 0,
            loading: false,
            loadingPdf: false,
            loadingXlsx: false,
            resource: 'inventory2',
            errors: {},
            form: {},
            warehouses: [],
            categories: [],
            brands: [],
            filters: [],
            records:[],
            records_supplies: [],
            totals: {
                purchase_unit_price: 0,
                sale_unit_price: 0,
            },
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM-DD')
                    return this.form.date_start > time
                }
            },
            item_selected:{},
            item_selected_amount:null,
            pagination: {},
        }
    },
    created() {
        this.$eventHub.$on("reloadData", () => {
        });
    },
    async mounted() {
        await this.getRecordsSupplies();
    },
    methods: {
        close(){
            this.initForm()
            this.$emit('update:showDialog', false)
        },
        initForm(){
            this.form = {};
            this.item_selected={};
            this.item_selected_amount=0;
        },
        
        getRecordsSupplies(){
            this.loading_submit = true;
            return this.$http
                .get(`/recipescosts/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records_supplies = this.parseValues(response.data.data) ;
                    this.records_supplies = this.records_supplies.filter(e=>e.type_doc == 'recipesub')
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
        },
        parseValues(data){
           try {
            for (let i = 0; i < data.length; i++) {
                data[i].subrecipes_supplies = JSON.parse(data[i].subrecipes_supplies);
                data[i].quantity_current = this.getQuantitySubReceta(data[i].subrecipes_supplies);
                data[i].cif = JSON.parse(data[i].cif);
                data[i].costs = JSON.parse(data[i].costs);
            }
            return data ;
           } catch (e) {
            console.log(e);
           }
        },
        getQuantitySubReceta(params){
            let amount = 0;
            for (let i = 0; i < params.length; i++) {
                amount = amount + Number(params[i].quantity)
            }
            return amount ;
        },
        getQueryParameters(){
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
                isPharmacy:this.fromPharmacy,
                ...this.search
            });
        },
       
        limitQuantitySuppliesRequest(value){

        },
        addRegister(){
            this.loading_submit = true;
            return this.$http
                .post(`/${this.resource}`,{quantity:this.item_selected_amount,id_subrecipe:this.item_selected.id,})
                .then(response => {
                    if(response.data.success){
                        this.$message({
                                    showClose: true,
                                    message: `${response.data.data.message}`,
                                    duration: 1 * 2000,
                                    type: "success"
                                });
                        let info_add = {name:this.item_selected.name,quantity:this.item_selected_amount};
                        this.$emit('add',info_add )
                        this.item_selected_amount=0
                        this.item_selected={}
                        this.close()
                    }
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
        }
    }
}
</script>
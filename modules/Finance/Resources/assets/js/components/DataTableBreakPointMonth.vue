<template>
    <div>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-12 ">
                        <h3 class="pl-5">GASTOS INDIRECTOS</h3>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Gasto_Indirecto</th>
                                        <th scope="col">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in records">
                                        <td @click="selectItem(row)">{{ row.name }}</td>
                                        <td>{{ row.amount }}</td>
                                    </tr>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <label class="pl-2" for="">TOTAL GASTOS INDIRECTOS S/ <strong> {{ totals }}</strong></label>
                <br>
                <label class="pl-2" for="">TIPO DE GASTO INDIRECTO </label>
                <br>
                <label for="" class="pl-4"> <strong>{{ indirect_expense.name }}</strong>
                </label>
                <br>
                <label class="pl-2" for="">MONTO PROMEDIO MENSUAL:</label>
                <br>
                <label for="" class="pl-4"> 
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">S/</span>
                        </div>
                        <input type="number" class="form-control" aria-label="" v-model="indirect_expense.amount">
                    </div>
                    <el-button type="primary" native-type="submit" @click="submit()" >Guardar</el-button>

                </label>
            </div>
            <div class="col-12 col-md-8">
                <div class="row">
                    <div class="col-md-12 col-lg-12 col-xl-12 ">
                        <h3 class="pl-5">LISTA DE PRODUCTOS MAS VENDIDOS</h3>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <el-table :data="currentTableData"
                                :default-sort="{ prop: 'date_of_payment', order: 'ascending' }" show-summary
                                style="width: 100%">

                                <el-table-column label="Producto" prop="person_name" sortable>
                                    <!--label=Adquiriente-->
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.person_name }}
                                            <br />
                                            <small>{{ scope.row.person_number }}</small>
                                        </span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Cantidad" prop="document_type_description" sortable>
                                    <!--label="Documento/Transacción"-->
                                    <template slot-scope="scope">
                                        <span>
                                            {{ scope.row.number_full }}<br />
                                            <small v-text="scope.row.document_type_description"></small>
                                        </span>

                                    </template>
                                </el-table-column>
                                <el-table-column label="Cod_Producto" prop="document_type_description" sortable>
                                    <!--label="Documento/Transacción"-->
                                    <template slot-scope="scope">
                                        <span>
                                            {{ scope.row.number_full }}<br />
                                            <small v-text="scope.row.document_type_description"></small>
                                        </span>

                                    </template>
                                </el-table-column>
                                <el-table-column label="Precio Venta" prop="document_type_description" sortable>
                                    <!--label="Documento/Transacción"-->
                                    <template slot-scope="scope">
                                        <span>
                                            {{ scope.row.number_full }}<br />
                                            <small v-text="scope.row.document_type_description"></small>
                                        </span>

                                    </template>
                                </el-table-column>
                                <el-table-column label="Margen de Contribucion" prop="document_type_description" sortable>
                                    <!--label="Documento/Transacción"-->
                                    <template slot-scope="scope">
                                        <span>
                                            {{ scope.row.number_full }}<br />
                                            <small v-text="scope.row.document_type_description"></small>
                                        </span>

                                    </template>
                                </el-table-column>
                                <el-table-column label="Porcentaje MC" prop="document_type_description" sortable>
                                    <!--label="Documento/Transacción"-->
                                    <template slot-scope="scope">
                                        <span>
                                            {{ scope.row.number_full }}<br />
                                            <small v-text="scope.row.document_type_description"></small>
                                        </span>

                                    </template>
                                </el-table-column>
                            </el-table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-12">
                    <div class="row">
                        <div class="col-12 col-md-4 m-0">
                            <span><h4 class="mb-3 text-center" style="color: red;" >VALORES PARA LLEGAR AL P.D.E</h4></span>
                            <div>
                                <p class="m-0">venta bruta mensual:</p>
                                <div>
                                    <span>S/ <strong>{{ venta_bruta_mensual_1 }}</strong> </span>
                                </div>
                                <p class="m-0"># de Ventas Promedio :</p>
                                <div>
                                    <span><strong> {{ numero_ventas_promedio_1 }}</strong> </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <span><h4 class="mb-3 text-center" style="color: red;">VALORES REGISTRADOS EN EL MES</h4></span>
                            <div>
                                <p class="m-0">venta bruta:</p>
                                <div>
                                    <span>S/ <strong>{{ venta_bruta_2 }}</strong> </span>
                                </div>
                                <p class="m-0"># de Ventas Promedio :</p>
                                <div>
                                    <span>S/ <strong>{{ numero_ventas_promedio_2  }}</strong> </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <span><h4 class="mb-3 text-center" style="color: red;">VALORES FALTANTES PARA LLEGAR AL P.D.E</h4></span>
                            <div>
                                <p class="m-0">venta bruta:</p>
                                <div>
                                    <span>S/ <strong>{{ venta_bruta_3 }}</strong> </span>
                                </div>
                                <p class="m-0"># de Ventas Promedio:</p>
                                <div>
                                    <span><strong>{{ numero_ventas_promedio_3 }}</strong> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="col-12 col-md-12">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <h4>VALORES ADICIONALES REGISTRADOS</h4>
                            <div>
                                <p class="m-0" style="font-size:13px">VALOR DE VENTA PROMEDIO:</p>
                                <div>
                                    <span>S/ <strong> {{ (valores_faltantes_para_llegar_pde/ 3).toFixed(2) }}</strong></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div>
                                <p class="m-0" style="font-size:13px">PROMEDIO % MARGEN DE CONTRIBUCION DE PRODUCTOS:</p>
                                <div>
                                    <span>S/ <strong> {{ (valores_faltantes_para_llegar_pde/ 3).toFixed(2) }}</strong> %</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div>
                                <p class="m-0" style="font-size:13px">PROMEDIO DE VENTAS ULTIMO MES:</p>
                                <div>
                                    <span>S/ <strong> {{ (valores_faltantes_para_llegar_pde/ 3).toFixed(2) }}</strong></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>


    </div>
</template>
<style>
.font-custom {
    font-size: 15px !important
}
</style>
<script>

import moment from 'moment'
import queryString from 'query-string'

export default {
    props: {
        resource: String,
        filter: {
            type: Object,
            required: false,
            default: false
        },
        ismovements: {
            type: Number,
            required: false,
            default: 1
        },
        applyCustomer: {
            type: Boolean,
            required: false,
            default: false
        }
    },
    data() {
        return {
            filterdata: {
                column: null,
                order: null
            },
            indirect_expense:{id:'',name:"",amount:0},
            current_page: 1, // current page
            currentPage: 1, // current page
            per_page: 10,
            loading_submit: false,
            loading_search: false,
            links: {},
            columns: [],
            records: [],
            currentTableData: [],
            headers: headers_token,
            pagination: {},
            search: {},
            payment_types: [],
            destination_types: [],
            form: {},
            totals: 0,
            pickerOptionsDates: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM-DD')
                    return this.form.date_start > time
                }
            },
            pickerOptionsMonths: {
                disabledDate: (time) => {
                    time = moment(time).format('YYYY-MM')
                    return this.form.month_start > time
                }
            },
            sellers: [],
            config: {},
            venta_bruta_mensual_1:0,
            numero_ventas_promedio_1:0,
            venta_bruta_2:0,
            numero_ventas_promedio_2:0,
            venta_bruta_3:0,
            numero_ventas_promedio_3:0,
            valores_faltantes_para_llegar_pde:0
        }
    },
    computed: {
        showPagination: function () {
            if (this.per_page === 'todos') return false;

            if (this.records.length < this.currentTableData.length) {
                return false
            }
            if (this.records.length < this.per_page) {
                return false
            }
            return true
        },
        itemsPerPage: function () {
            if (this.per_page === 'todos') {
                return this.records.length
            }
            return this.per_page
        },
        showDestination: function () {
            return !(this.ismovements !== undefined && this.ismovements === 0);
        },
    },
    created() {

        //item_per_page
        this.initForm()
        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
    },
    async mounted() {
        await this.getRecords()
    },
    methods: {
        initForm() {

            this.form = {
                payment_type: null,
                destination_type: null,
                period: 'between_months',
                date_start: moment().format('YYYY-MM-DD'),
                date_end: moment().format('YYYY-MM-DD'),
                month_start: moment().format('YYYY-MM'),
                month_end: moment().format('YYYY-MM'),
                last_cash_opening: false,
            }
        },
        
        
        getRecords() {
            let d_start = moment().startOf('month').format('YYYY-MM-DD');
            let d_end = moment().endOf('month').format('YYYY-MM-DD');
            let query = "d_start=" + d_start + "&d_end=" + d_end;
            return this.$http.get(`/${this.resource}/records?${query}`).then((response) => {
                this.records = response.data.records
                this.venta_bruta_mensual_1 = response.data.venta_bruta_mensual
                this.numero_ventas_promedio_1 = response.data.numero_ventas_promedio
                this.venta_bruta_2 = response.data.venta_bruta_2
                this.numero_ventas_promedio_2 = response.data.numero_ventas_promedio_2
                this.venta_bruta_3 = response.data.venta_bruta_3
                this.numero_ventas_promedio_3 = response.data.numero_ventas_promedio_3
                this.valores_faltantes_para_llegar_pde = Number(response.data.ventas_totales)
            }).finally(() => {
                this.loading_submit = false
            });
        },
        reindex_array_keys(array, start) {
            var temp = [];
            start = typeof start == 'undefined' ? 0 : start;
            start = typeof start != 'number' ? 0 : start;
            for (var i in array) {
                array[i].index = parseInt(i) + 1;
                temp[start++] = array[i];
            }
            return temp;
        },

        
        selectItem(item){
            this.indirect_expense.name = item.name;
            this.indirect_expense.amount = item.amount;
            this.indirect_expense.id = item.id
        },
        addValueToRecords(item){
            let index = this.records.findIndex(e=>e.id == item.id);
            if(index != -1) {
                this.records[index].amount = item.amount;
                this.calculateTotalGasto(this.records)
                this.monthlyGrossSales();
            }
        },
        submit(){
            this.addValueToRecords(this.indirect_expense);
            this.$http.post(`/${this.resource}/store`, this.indirect_expense)
                    .then(response => {
                        if (response.data.success) {
                            this.$message.success(response.data.message)
                        } else {
                            this.$message.error(response.data.message)
                        }
                        this.monthlyGrossSales()
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data
                        } else {
                            console.log(error)
                        }
                    })
                    .then(() => {
                        this.loading_submit = false
                    })
        }

    }
}
</script>

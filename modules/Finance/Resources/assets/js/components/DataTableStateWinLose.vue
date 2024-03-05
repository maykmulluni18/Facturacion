<template>
    <div>
        <div class="row">
            <div class="col-5">
                <div class="row">
                    
                    <template >
                        <div class="col-md-3">
                            <label class="control-label">Mes de</label>
                            <el-date-picker v-model="form.month_start" :clearable="false" format="MM/yyyy"
                                type="month" value-format="yyyy-MM"></el-date-picker>
                        </div>
                    </template>
                    <template >
                        <div class="col-md-3">
                            <label class="control-label">Mes al</label>
                            <el-date-picker v-model="form.month_end" :clearable="false"
                                format="MM/yyyy" type="month"
                                value-format="yyyy-MM"></el-date-picker>
                        </div>
                    </template>
                </div>
                <div class="col">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 m-0">
                            <p class="mb-1 text-center"> <strong>VENTA BRUTA:</strong> </p>

                            <div class="row no-gutters">
                                <div class="col-sm-6 col-md-8">Venta de Productos:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ {{ venta_producto }}</strong> </span></div>

                                <div class="col-sm-6 col-md-8">Venta de Servicios:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ 0.00</strong> </span></div>

                                <div class="col-sm-6 col-md-8">Otras Ventas:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ 0.00</strong> </span></div>

                            </div>

                        </div>

                        <div class="col-12 col-md-3" style="background: #ffffbf;">
                            <div>
                                <p class="mb-1 text-center"><strong>TOTAL DE VENTA BRUTA</strong> </p>
                                <div>
                                    <span> <strong>S/ {{ venta_producto }}</strong> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 m-0">
                            <p class="mb-1 text-center"> <strong>GASTOS DIRECTOS:</strong> </p>

                            <div class="row no-gutters">
                                <div class="col-sm-6 col-md-8">Materia Prima:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ {{ materia_prima }}</strong> </span></div>

                                <div class="col-sm-6 col-md-8">Labor Directa:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ {{ labor_directa }}</strong> </span></div>

                                <div class="col-sm-6 col-md-8">Comisiones:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ {{ comisiones }}</strong> </span></div>

                                <div class="col-sm-6 col-md-8">Transaporte/Envio/correo:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ {{ transporte }}</strong> </span></div>

                                <div class="col-sm-6 col-md-8">Otros Gastos Directos:</div>
                                <div class="col-6 col-md-4"><span> <strong>S/ {{ other_expense }}</strong> </span></div>

                            </div>

                        </div>

                        <div class="col-12 col-md-3" style=" background: #ffffbf;">
                            <div>
                                <p class="mb-1 text-center"> <strong>TOTAL DE GASTOS DIRECTOS</strong> </p>
                                <div>
                                    <span><strong>S/ {{ total_gastos_directos }}</strong> </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container">
                    <div class="row">
                        <div class="col-9 text-center" style="background: #ffffbf;">
                            <h4> <strong>UTILIDAD BRUTA</strong> </h4>
                            <h5><strong>S/ {{ utilidad_bruta }}</strong></h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-7">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-8">
                            <strong>GASTOS INDIRECTOS:</strong> 
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col"></th>
                                                <th scope="col"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="row in records">
                                                <td >{{ row.name }}</td>
                                                <td>{{ row.amount }}</td>
                                            </tr>
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div style=" background: #ffffbf;">
                                    <p class="mb-1 text-center"> <strong>TOTAL DE GASTOS INDIRECTOS</strong> </p>
                                    <div>
                                        <span><strong>S/ {{ total_gastos_indirectos }}</strong> </span>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-9 text-center" style="background: #ffffbf;">
                            <h4> <strong>UTILIDAD NETA</strong> </h4>
                            <h5><strong>S/ {{ utilidad_neta }}</strong></h5>
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
        configuration: {
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
            current_page: 1, // current page
            currentPage: 1, // current page
            per_page: 10,
            loading_submit: false,
            loading_search: false,
            links: {},
            columns: [],
            records: [],
            venta_producto: 0,
            materia_prima: 0,
            labor_directa:0,
            comisiones:0,
            transporte:0,
            other_expense:0,
            total_gastos_indirectos:0,
            currentTableData: [],
            headers: headers_token,
            pagination: {},
            search: {},
            payment_types: [],
            destination_types: [],
            form: {},
            totals: {},
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
            total_gastos_directos:0,
            utilidad_bruta:0,
            utilidad_neta:0
        }
    },
    computed: {
        
    },
    created() {
        this.form.month_start = moment().subtract(1,"month").format("YYYY-MM-DD") ;
        this.form.month_end = moment().format("YYYY-MM-DD");

        this.$eventHub.$on('reloadData', () => {
            this.getRecords()
        })
    },
    async mounted() {
        await this.getRecords()
    },
    methods: {
        getRecords() {
            let query =  "date_start=" + this.form.month_start + "&date_end="+this.form.month_end
            return this.$http.get(`/${this.resource}/records?${query}`).then((response) => {
                this.venta_producto = (Number(Number(response.data.total)).toFixed(2))
                this.materia_prima = (Number(Number(response.data.totals_buys)).toFixed(2))
                this.labor_directa = (Number(Number(response.data.totals_labor_directa)).toFixed(2))
                this.comisiones = (Number(Number(response.data.total_comision)).toFixed(2))
                this.transporte = (Number(Number(response.data.total_transp)).toFixed(2))
                this.other_expense = (Number(Number(response.data.total_others_expense)).toFixed(2))
                this.records = response.data.records_expense_indirect
                this.total_gastos_indirectos = this.records.map(item => item.amount).reduce((prev, curr) => prev + curr, 0)
                this.getTotalGastosDirectos();
                this.getUtilidadNeta()
            }).finally(() => {
                this.loading_submit = false
            });
        },
        getUtilidadNeta(){
            this.utilidad_bruta = (this.venta_producto -  this.total_gastos_directos).toFixed(2) ;
            this.utilidad_neta = (this.utilidad_bruta - this.total_gastos_indirectos).toFixed(2);
        },
        getTotalGastosDirectos(){
 
            this.total_gastos_directos = Number(this.materia_prima)+ Number(this.labor_directa) + Number(this.comisiones) + Number(this.transporte) + Number(this.other_expense);
        }
    }
}
</script>

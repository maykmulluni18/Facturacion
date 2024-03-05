<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">
                {{ currentTitle }}
            </h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="row">
                        <label for="">Desde: </label>
                        <div class="col">
                            <div class="d-flex">
                                <el-date-picker v-model="form.from" type="date" value-format="yyyy-MM-dd"
                                    format="yyyy-MM-dd" :clearable="false">
                                </el-date-picker>
                            </div>
                        </div>
                        <label for="">Hasta: </label>
                        <div class="col">
                            <div class="d-flex">
                                <el-date-picker v-model="form.to" type="date" value-format="yyyy-MM-dd" format="yyyy-MM-dd"
                                    :clearable="false">
                                </el-date-picker>
                            </div>
                        </div>
                        <div class="col">
                            <div class="btn-group mr-2" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-primary" @click.prevent="getRecordsProductosMasVendidos()">Buscar</button>
                            </div>
                        </div>
                </div>
                <div class="col-md-12 m-b-10">
                    <label>Productos Mas Vendidos</label>
                </div>
                <x-graph-line :all-data="records_items.graph"></x-graph-line>
                <hr>
                <div class="row">
                        <label for="">Desde: </label>
                        <div class="col">
                            <div class="d-flex">
                                <el-date-picker v-model="form2.from" type="date" value-format="yyyy-MM-dd"
                                    format="yyyy-MM-dd" :clearable="false">
                                </el-date-picker>
                            </div>
                        </div>
                        <label for="">Hasta: </label>
                        <div class="col">
                            <div class="d-flex">
                                <el-date-picker v-model="form2.to" type="date" value-format="yyyy-MM-dd" format="yyyy-MM-dd"
                                    :clearable="false">
                                </el-date-picker>
                            </div>
                        </div>
                        <div class="col">
                            <div class="btn-group mr-2" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-primary" @click.prevent="getRecordsInsumosMasMovidos()">Buscar</button>
                            </div>
                        </div>
                </div>
                <hr>

                <div class="col-md-12 m-b-10">
                    <label>Insumos Mas Vendidos</label>
                </div>
                <x-graph-line :all-data="records_supplies.graph"></x-graph-line>

                <div class="col-md-12 m-b-10">
                    <label>Punto de Equilibrio</label>
                </div>
                <x-graph-line :all-data="records_breakpoints.graph"></x-graph-line>
                
                <div class="col-md-12 m-b-10">
                    <label>Categorías </label>
                    <select v-model="form_gastos_varios" class="form-control form-control-sm">
                        <option v-for=" row in records_gastos_varios" :value="row"> {{ row.description }}</option>
                    </select>
                    <div class="col">
                        <div class="form-actions text-right pt-2 mt-2">
                            <el-button :loading="loading_submit" @click.prevent="getRecordsPorcentajeCategoriaBySales()"
                                type="primary">Buscar
                            </el-button>
                        </div>
                    </div>
                </div>
                <x-graph-line :all-data="records_porcentaje_6_meses.graph"></x-graph-line>
                
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

export default {
    components: {
        
    },
    props: [
        'configuration',
        'ismovements',
    ],
    data() {
        return {
            title:'Grafico de Tendencia',
            resource: "finances/trendgraph",
            form: {from:moment().subtract(1,"month").format("YYYY-MM-DD"),to:moment().format("YYYY-MM-DD")},
            form2: {from:moment().subtract(1,"month").format("YYYY-MM-DD"),to:moment().format("YYYY-MM-DD")},
            filter: {
                column: '',
                order: ''
            },
            records_items:{},
            records_supplies:{},
            records_breakpoints:{},
            records_porcentaje_6_meses:{},
            records_gastos_varios : {},
            form_gastos_varios:{}
        };
    },computed:{
        
        
        currentTitle:function(){
            this.title = 'Grafico de Tendencia';
            return this.title
        }
    },
    created() {
        this.currentTitle
        this.getRecordsProductosMasVendidos()
        this.getRecordsInsumosMasMovidos()
        this.getRecordsBreakPoint()
        this.getGastosVarios();
        this.getRecordsPorcentajeCategoriaBySales()
    },
    methods: {
        
        getRecordsProductosMasVendidos(){
            let query = "d_start=" + this.form.from + "&d_end=" + this.form.to;
            return this.$http.get(`/${this.resource}/records?${query}`).then((response) => {
               console.log("response ",response);
               this.records_items = response.data.productos_mas_vendidos
            }).finally(() => {
                this.loading_submit = false
            });
        },
        getGastosVarios(){
            return this.$http.get(`/${this.resource}/recordscategory`).then((response) => {
               console.log("response ksjd",response);
               this.records_gastos_varios = response.data.data
            }).finally(() => {
                this.loading_submit = false
            });
        },
        getRecordsInsumosMasMovidos(){
            let query = "d_start=" + this.form2.from + "&d_end=" + this.form2.to;
            return this.$http.get(`/${this.resource}/records?${query}`).then((response) => {
               console.log("response ",response);
               this.records_supplies = response.data.insumos_mas_vendidos
            }).finally(() => {
                this.loading_submit = false
            });
        },
        getRecordsBreakPoint(){
            let query = "d_start=" + moment().subtract(6,"month").format("YYYY-MM-DD") + "&d_end=" + moment().format("YYYY-MM-DD");
            return this.$http.get(`/${this.resource}/recordsbreakpoint?${query}`).then((response) => {
               console.log("response ",response);
               this.records_breakpoints = response.data.breakpoints
            }).finally(() => {
                this.loading_submit = false
            });
        },
        getRecordsPorcentajeCategoriaBySales(){
            let query = "d_start=" + moment().subtract(6,"month").format("YYYY-MM-DD") + "&d_end=" + moment().format("YYYY-MM-DD") + "&expense_id="+ (this.records_gastos_varios.length > 0 ?this.form_gastos_varios.id:4) ;
            return this.$http.get(`/${this.resource}/recordscategorybysales?${query}`).then((response) => {
               console.log("response ",response);
               this.records_porcentaje_6_meses = response.data.records_porcentaje_6_meses
            }).finally(() => {
                this.loading_submit = false
            });
        }
    }
};
</script>

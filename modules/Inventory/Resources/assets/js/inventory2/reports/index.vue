<template>
    <div class="row card-table-report">
        <div class="col-md-12">
            <div
                 class="card card-primary" v-loading="loading">
                <div class="card-header bg-info">
                    <h4 class="card-title">Reporte de Inventario Nivel 2</h4>
                </div>
                
                <div class="card-body">
                    <template>
                        <div class="btn-group flex-wrap">
                            <button aria-expanded="false" class="btn btn-custom btn-sm mt-2 mr-2 dropdown-toggle" data-toggle="dropdown" type="button">
                                <i class="fa fa-download"></i> Exportar
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu" style=" position: absolute; will-change: transform; top: 0px;left: 0px;transform: translate3d(0px, 42px, 0px);"
                                x-placement="bottom-start">
                                <a
                                    class="dropdown-item text-1"
                                    href="#"
                                    @click.prevent="clickExport()"
                                >Listado</a
                                >
                            </div>
                        </div>
                    </template>
                    <div class="right-wrapper pull-right">
                        <button
                                class="btn btn-custom btn-sm mt-2 mr-2"
                                type="button"
                                @click.prevent="clickCreate()"
                            >
                                <i class="fa fa-plus-circle"></i> Nuevo
                            </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-responsive-xl table-bordered table-hover">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Cantidad (gr)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(row , index) in records" :key="index">
                                            <th scope="row">{{ index+1 }}</th>
                                            <td>{{ row.name }}</td>
                                            <td>{{ row.quantity + " gr" }}</td>
                                            </tr>
                                            
                                        </tbody>
                                </table>
                                <div>
                                    <el-pagination
                                            @current-change="getRecords"
                                            layout="total, prev, pager, next"
                                            :total="pagination.total"
                                            :current-page.sync="pagination.current_page"
                                            :page-size="pagination.per_page">
                                    </el-pagination>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            Total {{ records.length }}
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        <form-item :showDialog.sync="showDialogAddItem" @add="addRow">
        </form-item>
        <items-export :showDialog.sync="showExportDialog"></items-export>

    </div>

</template>

<script>

import moment from "moment";
import queryString from "query-string";
import FormItem from './form.vue'
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
import ItemsExport from "../partials/export.vue";

export default {
    props: [],
    components:{FormItem,ItemsExport},
    data() {
        return {
            // loading_submit: false,
            // showDialogLots: false,
            // showDialogLotsOutput: false,
            // titleDialog: null,
            total_profit: 0,
            total_all_profit: 0,
            loading: false,
            loadingPdf: false,
            loadingXlsx: false,
            resource: 'recipescosts',
            errors: {},
            form: {},
            showExportDialog: false,
            warehouses: [],
            categories: [],
            brands: [],
            filters: [],
            records: [],
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
            pagination: {},
            showDialogAddItem:false
        }
    },
    created() {
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
    },
    mounted() {
        this.getRecords();
    },
    methods: {
        changeDisabledDates() {
            if (this.form.date_end < this.form.date_start) {
                this.form.date_end = this.form.date_start
            }
            this.getRecords();
        },
        initTotals() {

            this.totals = {
                purchase_unit_price: 0,
                sale_unit_price: 0,
            }

        },
        initForm() {
            this.form = {
                'warehouse_id': null,
                'filter': '01',
                'category_id': null,
                'brand_id': null,
                active: null
            }
        },
        
        clickCreate(){
            this.showDialogAddItem=true;
        },
        addRow(row) {
            this.getRecords()
        },
        initTables() {
            this.$http.get(`/${this.resource}/tables`)
                .then(response => {
                    this.warehouses = response.data.warehouses;
                    this.brands = response.data.brands;
                    this.categories = response.data.categories;
                });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: this.pagination.current_page,
                limit: this.limit,
                ...this.form
            });
        },
        async getRecords() {

            this.loading = true

            await this.$http.get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = this.parseValues(response.data.data) ;
                    this.records = this.records.filter(e=>e.type_doc == 'recipesub')
                    //this.pagination = response.data.meta
                    //this.pagination.per_page = parseInt(response.data.meta.per_page)
                    //this.calculeTotalProfit()
                })
            this.loading = false;
        },
        parseValues(data){
           try {
            for (let i = 0; i < data.length; i++) {
                data[i].subrecipes_supplies = JSON.parse(data[i].subrecipes_supplies);
                data[i].cif = JSON.parse(data[i].cif);
                data[i].costs = JSON.parse(data[i].costs);
            }
            return data ;
           } catch (e) {
            console.log(e);
           }
        },
        clickExport() {
            this.showExportDialog = true;
        },
    }
}
</script>

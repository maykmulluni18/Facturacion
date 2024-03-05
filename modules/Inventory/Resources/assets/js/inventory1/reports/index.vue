<template>
    <div class="row card-table-report">
        <div class="col-md-12">
            <div class="right-wrapper pull-right">
                <button
                    v-if="can_add_new_product"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    type="button"
                    @click.prevent="clickCreate()"
                >
                    <i class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
            <div class="card card-primary" v-loading="loading">
                <div class="card-header bg-info">
                    <h4 class="card-title">Reporte de Inventario Nivel 1</h4>
                    <div class="data-table-visible-columns"
                         style="top:10px">
                        <el-dropdown :hide-on-click="false">
                            <el-button type="primary">
                                Mostrar/Ocultar filtros<i class="el-icon-arrow-down el-icon--right"></i>
                            </el-button>
                            <el-dropdown-menu slot="dropdown">
                                <el-dropdown-item v-for="(column, index) in filters"
                                                  :key="index">
                                    <el-checkbox v-model="column.visible">{{ column.title }}</el-checkbox>
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </el-dropdown>
                    </div>
                </div>
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
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-responsive-xl table-bordered table-hover">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Cantidad</th>
                                            <th scope="col">Categoria</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(row, index) in records" :key="index">
                                            <th scope="row">{{ index+1 }}</th>
                                            <td>{{ row.name }}</td>
                                            <td>{{ row.quantity }}</td>
                                            <td>{{ row.category_name }}</td>
                                            </tr>
                                        </tbody>
                                        </table>
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
        <items-export :showDialog.sync="showExportDialog"></items-export>

    </div>
</template>

<script>

import moment from "moment";
import queryString from "query-string";
import ItemsExport from "../partials/export.vue";

export default {
    props: [],
    components: {
        ItemsExport
    },
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
            showExportDialog: false,
            loadingXlsx: false,
            resource: 'supplies',
            errors: {},
            form: {},
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
            productType:{},
            search:{},
            pagination: {},
            fromPharmacy:false
        }
    },
    created() {
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
    },
    async mounted() {
        await this.getRecords();
    },
    methods: {
        getRecords(){
            this.loading_submit = true;
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = response.data.data;
                    //this.pagination = response.data.meta;
                    //this.pagination.per_page = parseInt(
                        //response.data.meta.per_page
                    //);
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
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
        clickExport() {
            this.showExportDialog = true;
        },
        
    }
}
</script>

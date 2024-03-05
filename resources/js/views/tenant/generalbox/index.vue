<template>
    <div>
        <div class="page-header pr-0">
            <h2>
                <a href="/dashboard"><i class="fas fa-tachometer-alt"></i></a>
            </h2>
            <ol class="breadcrumbs">
                <li class="active"><span>{{ titleTopBar }}</span></li>
            </ol>

            <div class="right-wrapper pull-right">
                <template v-if="typeUser === 'admin'">
                    <div class="btn-group flex-wrap">
                        <button
                            aria-expanded="false"
                            class="btn btn-custom btn-sm mt-2 mr-2 dropdown-toggle"
                            data-toggle="dropdown"
                            type="button"
                        >
                            <i class="fa fa-download"></i> Exportar
                            <span class="caret"></span>
                        </button>
                        <div
                            class="dropdown-menu"
                            role="menu"
                            style="
                                position: absolute;
                                will-change: transform;
                                top: 0px;
                                left: 0px;
                                transform: translate3d(0px, 42px, 0px);
                            "
                            x-placement="bottom-start"
                        >
                            <a
                                class="dropdown-item text-1"
                                href="#"
                                @click.prevent="clickExport()"
                            >Listado</a>
                        </div>
                    </div>
                </template>
                <button
                    v-if="can_add_new_product"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    type="button"
                    @click.prevent="clickCreate()"
                >
                    <i class="fa fa-plus-circle"></i> Nuevo
                </button>
            </div>
        </div>

        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">{{ title }}</h3>
            </div>
            <div class="card-body">
                <div class="col-md-12 col-lg-12 col-xl-12 ">

                    <div class="row mt-2">
                        <div class="col-md-3">
                            <label class="control-label">Periodo
                                <el-tooltip class="item" content="Filtra por fecha de pago" effect="dark"
                                            placement="top-start">
                                    <i class="fa fa-info-circle"></i>
                                </el-tooltip>
                            </label>
                            <el-select v-model="form.period" @change="changePeriod">
                                <el-option key="between_months" label="Entre meses" value="between_months"></el-option>
                            </el-select>
                        </div>
                        <template v-if="form.period === 'between_months' || form.period === 'between_months'">
                            <div class="col-md-3">
                                <label class="control-label">Mes de</label>
                                <el-date-picker v-model="form.month_start" :clearable="false" format="MM/yyyy" type="month"
                                                value-format="yyyy-MM" @change="changeDisabledMonths"></el-date-picker>
                            </div>
                        </template>
                        <template v-if="form.period === 'between_months'">
                            <div class="col-md-3">
                                <label class="control-label">Mes al</label>
                                <el-date-picker v-model="form.month_end" :clearable="false"
                                                :picker-options="pickerOptionsMonths" format="MM/yyyy" type="month"
                                                value-format="yyyy-MM"></el-date-picker>
                            </div>
                        </template>

                        <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                            <el-button :loading="loading_submit" class="submit" icon="el-icon-search" type="primary"
                                       @click.prevent="getRecordsByFilter">Buscar
                            </el-button>
                        </div>
                        <div class="col-lg-7 col-md-7 col-md-7 col-sm-12" style="margin-top:29px">
                        <span>
                            <strong>SALDO ACTUAL: S/ 45345</strong>
                        </span>
                        </div>

                    </div>
                    <div class="row mt-1 mb-4">
                    </div>
                </div>
                <div class="col-md-12">

                    <div class="pull-right">
                        <el-select v-model="per_page" @change="handleCurrentChange">
                            <el-option key="10" label="10" value="10"></el-option>
                            <el-option key="15" label="15" value="15"></el-option>
                            <el-option key="25" label="25" value="25"></el-option>
                            <el-option key="50" label="50" value="50"></el-option>
                            <el-option key="todos" label="Todos" value="todos"></el-option>
                        </el-select>
                    </div>
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Descrip. Movimiento</th>
                                    <th>Cat. Movimiento</th>
                                    <th>Medio</th>
                                    <th>Tipo de Movimiento</th>
                                    <th>Fecha de Movimiento</th>
                                    <th>Cantidad <strong>S/</strong></th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(row, index) in records" :key="index">
                                    <th>{{ index + 1 }}</th>
                                    <th>{{ row.description_movement }}</th>
                                    <th>{{ row.category_movement }}</th>
                                    <th>{{ row.half_spent }}</th>
                                    <th>{{ row.type_movement == 1?'Gasto':'Ingreso' }}</th>
                                    <th>{{ row.date_movement }}</th>
                                    <th>S/ {{ row.amount_movement }}</th>
                                    <th class="text-right">
                                        <div class="dropdown">
                                            <button id="dropdownMenuButton"
                                                    aria-expanded="false"
                                                    aria-haspopup="true"
                                                    class="btn btn-default btn-sm"
                                                    data-toggle="dropdown"
                                                    type="button">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div aria-labelledby="dropdownMenuButton"
                                                 class="dropdown-menu">

                                                <template >
                                                    <button
                                                        class="dropdown-item"
                                                        @click.prevent="clickDelete(row.id)"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </th>

                                </tr>
                                </tbody>
                            </table>
                            <div>
                                <el-pagination
                                    @current-change="getRecords"
                                    layout="total, prev, pager, next"
                                    :total="pagination.total"
                                    :current-page.sync="pagination.current_page"
                                    :page-size="pagination.per_page"
                                >
                                </el-pagination>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <items-form
                :recordId="recordId"
                :showDialog.sync="showDialog"
                :type="type"
            ></items-form>

            <items-import :showDialog.sync="showImportDialog"></items-import>
            <items-export :showDialog.sync="showExportDialog"></items-export>
            <items-export-wp
                :showDialog.sync="showExportWpDialog"
            ></items-export-wp>

            <items-export-barcode
                :showDialog.sync="showExportBarcodeDialog"
            ></items-export-barcode>

            <items-export-extra
                :showDialog.sync="showExportExtraDialog"
            ></items-export-extra>
            <warehouses-detail
                :item_unit_types="item_unit_types"
                :showDialog.sync="showWarehousesDetail"
                :warehouses="warehousesDetail"
            >
            </warehouses-detail>

            <items-import-list-price
                :showDialog.sync="showImportListPriceDialog"
            ></items-import-list-price>

            <items-import-extra-info
                :showDialog.sync="showImportExtraWithExtraInfo"
            ></items-import-extra-info>


            <items-import-update-price
                :showDialog.sync="showImporUpdatePrice"
            ></items-import-update-price>

            <!--
            : false,
            show_extra_info_to_item
            -->
            <tenant-item-aditional-info-modal
                :item="recordItem"
                :showDialog.sync="showDialogItemStock"
            ></tenant-item-aditional-info-modal>
            <items-history
                :recordId="recordId"
                :showDialog.sync="showDialogHistory"
            >
            </items-history>
        </div>
    </div>
</template>
<script>

import ItemsForm from "./form.vue";
import WarehousesDetail from "./partials/warehouses.vue";
import ItemsImport from "./import.vue";
import ItemsImportListPrice from "./partials/import_list_price.vue";
import ItemsImportExtraInfo from "./partials/import_list_extra_info.vue";
// resources/js/views/tenant/items/partials/import_list_extra_info.vue
import ItemsExport from "./partials/export.vue";
import ItemsExportWp from "./partials/export_wp.vue";
import ItemsExportBarcode from "./partials/export_barcode.vue";
import ItemsExportExtra from "./partials/export_extra.vue";
import {deletable} from "../../../mixins/deletable";
import ItemsHistory from "@viewsModuleItem/items/history.vue";
import {mapActions, mapState} from "vuex";
import ItemsImportUpdatePrice from "./partials/update_prices.vue";
import moment from "moment";
import queryString from 'query-string'


export default {
    props: [
        "configuration",
        "typeUser",
        "type"],
    mixins: [deletable],
    components: {
        ItemsForm,
        ItemsImport,
        ItemsExport,
        ItemsExportWp,
        ItemsExportBarcode,
        ItemsExportExtra,
        WarehousesDetail,
        ItemsImportListPrice,
        ItemsImportExtraInfo,
        ItemsHistory,
        ItemsImportUpdatePrice
    },
    data() {
        return {
            can_add_new_product: false,
            showDialog: false,
            showImportDialog: false,
            showExportDialog: false,
            showExportWpDialog: false,
            showExportBarcodeDialog: false,
            showExportExtraDialog: false,
            showImportListPriceDialog: false,
            showImportExtraWithExtraInfo: false,
            showImporUpdatePrice: false,
            showWarehousesDetail: false,
            resource: "generalbox",
            currentPage: 1, // current page
            recordId: null,
            recordItem: {},
            warehousesDetail: [],
            form:{},
            pagination:{},
            search:{},
            loading_submit: false,
            records:[],
            per_page: 10,
            currentTableData: [],
            columns:{},
            item_unit_types: [],
            titleTopBar: '',
            title: '',
            showDialogHistory: false,
            showDialogItemStock: false,
        };
    },
    created() {
        this.$store.commit('setConfiguration', this.configuration);
        this.loadConfiguration()

        if (this.config.is_pharmacy !== true) {
            delete this.columns.sanitary;
            delete this.columns.cod_digemid;
        }
        if (this.config.show_extra_info_to_item !== true) {
            delete this.columns.extra_data;

        }
        this.titleTopBar = 'Caja General';
        this.title = 'Caja General';
        this.$http.get(`/configurations/record`).then((response) => {
            this.$store.commit('setConfiguration', response.data.data);
            //this.config = response.data.data;
        });
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        this.canCreateProduct();
    },
    computed: {
        ...mapState([
            'config',
            'colors',
            'CatItemSize',
            'CatItemMoldCavity',
            'CatItemMoldProperty',
            'CatItemUnitBusiness',
            'CatItemStatus',
            'CatItemPackageMeasurement',
            'CatItemProductFamily',
            'CatItemUnitsPerPackage'
        ]),
        columnsComputed: function () {
            return this.columns;
        },
        itemsPerPage: function () {
            if (this.per_page === 'todos') {
                return this.records.length
            }
            return this.per_page
        },
    },
    async mounted() {
        let column_resource = _.split(this.resource, "/");
        await this.$http
            .get(`/${_.head(column_resource)}/columns`)
            .then(response => {
                this.columns = response.data;
                this.search.column = _.head(Object.keys(this.columns));
            });
        await this.getRecords();
    },
    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
        getSummaries(param) {
            const { columns, data } = param;
            const sums = [];
            columns.forEach((column, index) => {
                if (index < 6) {
                    sums[index] = '';
                    return;
                }

                const values = data.map(item => Number(item[column.property]));
                if (!values.every(value => isNaN(value))) {
                    let valor = values.reduce((prev, curr) => {
                        const value = Number(curr);
                        if (!isNaN(value)) {
                            return prev + curr;
                        } else {
                            return prev;
                        }
                    }, 0);

                    sums[index] = 'S/ ' + valor.toLocaleString('es')
                } else {
                    sums[index] = 'N/A';
                }
            });

            return sums;
        },
        changePeriod() {
            if (this.form.period === 'month') {
                this.form.month_start = moment().format('YYYY-MM');
                this.form.month_end = moment().format('YYYY-MM');
            }
            if (this.form.period === 'between_months') {
                this.form.month_start = moment().startOf('year').format('YYYY-MM'); //'2019-01';
                this.form.month_end = moment().endOf('year').format('YYYY-MM');

            }
            if (this.form.period === 'date') {
                this.form.date_start = moment().format('YYYY-MM-DD');
                this.form.date_end = moment().format('YYYY-MM-DD');
            }
            if (this.form.period === 'between_dates') {
                this.form.date_start = moment().startOf('month').format('YYYY-MM-DD');
                this.form.date_end = moment().endOf('month').format('YYYY-MM-DD');
            }
            // this.loadAll();
        },

        clickHistory(recordId) {
            this.recordId = recordId
            this.showDialogHistory = true
        },
        clickStockItems(row) {
            this.recordItem = row
            this.showDialogItemStock = true
        },
        canCreateProduct() {
            if (this.typeUser === 'admin') {
                this.can_add_new_product = true
            } else if (this.typeUser === 'seller') {
                if (this.config !== undefined && this.config.seller_can_create_product !== undefined) {
                    this.can_add_new_product = this.config.seller_can_create_product;
                }
            }
            return this.can_add_new_product;
        },
        duplicate(id) {
            this.$http
                .post(`${this.resource}/duplicate`, {id})
                .then((response) => {
                    if (response.data.success) {
                        this.$message.success(
                            "Se guardaron los cambios correctamente."
                        );
                        this.$eventHub.$emit("reloadData");
                    } else {
                        this.$message.error("No se guardaron los cambios");
                    }
                })
                .catch((error) => {
                });
            this.$eventHub.$emit("reloadData");
        },
        clickWarehouseDetail(warehouses, item_unit_types) {
            this.warehousesDetail = warehouses;
            this.item_unit_types = item_unit_types
            this.showWarehousesDetail = true;
        },
        clickCreate(recordId = null) {
            this.recordId = recordId;
            this.showDialog = true;
        },
        clickImport() {
            this.showImportDialog = true;
        },
        clickExport() {
            this.showExportDialog = true;
        },
        clickExportWp() {
            this.showExportWpDialog = true;
        },
        clickExportBarcode() {
            this.showExportBarcodeDialog = true;
        },
        clickExportExtra() {
            this.showExportExtraDialog = true;
        },
        clickImportListPrice() {
            this.showImportListPriceDialog = true;
        },
        clickImportExtraWithExtraInfo() {
            this.showImportExtraWithExtraInfo = true;
        },
        clickImportUpdatePrice(){
            this.showImporUpdatePrice = true;
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        clickDisable(id) {
            this.disable(`/${this.resource}/disable/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        clickEnable(id) {
            this.enable(`/${this.resource}/enable/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        handleCurrentChange() {
            this.currentTableData = this.records.slice(
                (this.currentPage - 1) * this.itemsPerPage,
                this.currentPage * this.itemsPerPage
            )
        },
        clickBarcode(row) {
            if (!row.barcode) {
                return this.$message.error(
                    "Para generar el código de barras debe registrar el código de barras."
                );
            }

            window.open(`/${this.resource}/barcode/${row.id}`);
        },
        clickPrintBarcode(row) {
            if (!row.barcode) {
                return this.$message.error(
                    "Para generar el código de barras debe registrar el código de barras."
                );
            }

            window.open(`/${this.resource}/export/barcode/print?id=${row.id}`);
        },
        clickPrintBarcodeX(row, x) {
            if (!row.barcode) {
                return this.$message.error(
                    "Para generar el código de barras debe registrar el código de barras."
                );
            }

            window.open(`/${this.resource}/export/barcode/print_x?format=${x}&id=${row.id}`);
        },
        getRecords() {
            this.loading_submit = true;
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = response.data.data;
                    this.pagination = response.data.meta;
                    this.pagination.per_page = parseInt(
                        response.data.meta.per_page
                    );
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
        },
        getQueryParameters() {
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

    },
};
</script>

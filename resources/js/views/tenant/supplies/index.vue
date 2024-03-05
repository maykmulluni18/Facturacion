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
                <template >
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
                            style="position: absolute; will-change: transform;top: 0px;left: 0px;transform: translate3d(0px, 42px, 0px);"
                            x-placement="bottom-start">
                            <a class="dropdown-item text-1" href="#" @click.prevent="clickExport()">Listado</a>
                        </div>
                    </div>
                </template>
                
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">{{ title }}</h3>
            </div>
            
            <div class="card-body">
                <div v-loading="loading_submit">
                    <div class="row ">
                        <div class="col-md-12 col-lg-12 col-xl-12 ">
                            <div class="row" v-if="applyFilter">
                                <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                                    <div class="d-flex">
                                        <div style="width:100px">
                                            Filtrar por:
                                        </div>
                                        <el-select
                                            v-model="search.column"
                                            placeholder="Select"
                                            @change="changeClearInput"
                                        >
                                            <el-option
                                                key="name"
                                                value="name"
                                                label="Nombre"
                                            ></el-option>
                                        </el-select>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-4 col-sm-12 pb-2">
                                    <template >
                                        <el-input
                                            placeholder="Buscar"
                                            v-model="search.value"
                                            style="width: 100%;"
                                            prefix-icon="el-icon-search"
                                            @input="getRecords"
                                        >
                                        </el-input>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Nombre Alternativo</th>
                                        <th>Precio Unitario</th>
                                        <th>Unidad</th>
                                        <th class="text-right">Categoria Insumo</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in records" :key="index">
                                        <th>{{ index + 1 }}</th>
                                        <th>{{ row.name }}</th>
                                        <th>{{ row.second_name }}</th>
                                        <th>{{ row.costs_unit }}</th>
                                        <th>{{ row.unit }}</th>
                                        <th class="text-right">{{ row.category_name }}</th>
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
import queryString from "query-string";

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
            can_add_new_product: true,
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
            resource: "supplies",
            recordId: null,
            recordItem: {},
            warehousesDetail: [],
            applyFilter: {
                type: Boolean,
                default: true,
                required: false
            },
            search: {
                value: null
            },
            records: [],
            pagination:{},
            loading_submit: false,
            columns:[],
            item_unit_types: [],
            titleTopBar: '',
            title: '',
            showDialogHistory: false,
            showDialogItemStock: false,
            pharmacy: Boolean,
            fromPharmacy: false,

        };
    },
    created() {
        this.$store.commit('setConfiguration', this.configuration);
        this.loadConfiguration()

       
        this.titleTopBar = 'Insumos';
        this.title = 'Listado de Insumo';

        this.$http.get(`/configurations/record`).then((response) => {
            this.$store.commit('setConfiguration', response.data.data);
            //this.config = response.data.data;
        });

        if(this.pharmacy !== undefined && this.pharmacy === true){
            this.fromPharmacy = true;
        }
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
        // this.canCreateProduct();
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
        
    },
    async mounted() {
        
        await this.getRecords();
    },
    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
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
                this.can_add_new_product = true
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
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        getRecords() {
            this.loading_submit = true;
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = response.data.data;
                    // this.pagination = response.data.meta;
                    // this.pagination.per_page = parseInt(
                        // response.data.meta.per_page
                    // );
                })
                .catch(error => {})
                .then(() => {
                    this.loading_submit = false;
                });
        },
        customIndex(index) {
            return (
                this.pagination.per_page * (this.pagination.current_page - 1) +
                index +
                1
            );
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

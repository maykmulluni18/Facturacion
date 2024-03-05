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
                    @click.prevent="clickCreate('recipe')"
                >
                    <i class="fa fa-plus-circle"></i> Nueva Receta
                </button>
                <button
                    v-if="can_add_new_product"
                    class="btn btn-custom btn-sm mt-2 mr-2"
                    type="button"
                    @click.prevent="clickCreate('recipesub')"
                >
                    <i class="fa fa-plus-circle"></i> Nueva Sub-Receta
                </button>
            </div>
        </div>
        <div class="card mb-0">
            <div class="card-header bg-info">
                <h3 class="my-0">{{ title }}</h3>
            </div>
            <div class="data-table-visible-columns">
                <el-dropdown :hide-on-click="false">
                    <el-button type="primary">
                        Mostrar/Ocultar columnas<i class="el-icon-arrow-down el-icon--right"></i>
                    </el-button>
                    <el-dropdown-menu slot="dropdown">
                        <el-dropdown-item v-for="(column, index) in columnsComputed"
                                          :key="index">
                            <el-checkbox
                                v-if="column.title !== undefined && column.visible !== undefined"
                                v-model="column.visible"
                            >{{ column.title }}
                            </el-checkbox>
                        </el-dropdown-item>
                    </el-dropdown-menu>
                </el-dropdown>
            </div>
            <div class="card-body">
                <div v-loading="loading_submit">
                    <div class="row ">
                        <div class="col-md-12 col-lg-12 col-xl-12 ">
                            <div class="row" v-if="applyFilter">
                                <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                                    <div class="d-flex">
                                        <div style="width:100px">
                                            Filtrar :
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
                                    
                                    <template v-if="search.column === 'name'">
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
                                        <th class="text-right">Tipo de Doc</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(row, index) in records" :key="index">
                                        <th>{{ index + 1 }}</th>
                                        <th>{{ row.name }}</th>
                                        <th class="text-right">{{ row.type_doc == 'recipesub' ?'SUB-RECETA':'RECETA' }}</th>
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
                                                            @click.prevent="clickCreate(row.id)"
                                                        >
                                                            Editar
                                                        </button>
                                                        <button v-if="row.type_doc == 'recipe'"
                                                            class="dropdown-item"
                                                            @click.prevent="clickDuplicate(row.id)"
                                                        >
                                                            Duplicar
                                                        </button>
                                                        <button
                                                            class="dropdown-item"
                                                            @click.prevent="clickExportRecipes(row.id)"
                                                        >
                                                            Exportar PDF
                                                        </button>
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

            </div>
            <items-form
                :recordId="recordId"
                :recipeId="recipeId"
                :showDialog.sync="showDialog"
                :type="type"
            ></items-form>
            <items-export :showDialog.sync="showExportDialog"></items-export>
            <items-recipes-export :idRecipe="idRecipe" :showDialog.sync="showExportRecipesDialog"></items-recipes-export>

            <!--
            : false,
            show_extra_info_to_item
            -->
            <tenant-item-aditional-info-modal
                :item="recordItem"
                :showDialog.sync="showDialogItemStock"
            ></tenant-item-aditional-info-modal>
            
        </div>
    </div>
</template>
<script>

import ItemsForm from "./form.vue";
// resources/js/views/tenant/items/partials/import_list_extra_info.vue
import ItemsExport from "./partials/export.vue";
import ItemsRecipesExport from "./partials/export_recipe.vue";
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
        ItemsExport,
        ItemsRecipesExport,
        ItemsHistory,
        ItemsImportUpdatePrice
    },
    data() {
        return {
            can_add_new_product: true,
            idRecipe:null,
            showDialog: false,
            showImportDialog: false,
            showExportDialog: false,
            showExportRecipesDialog: false,
            showExportWpDialog: false,
            showExportBarcodeDialog: false,
            showExportExtraDialog: false,
            showImportListPriceDialog: false,
            showImportExtraWithExtraInfo: false,
            showImporUpdatePrice: false,
            showWarehousesDetail: false,
            resource: "recipescosts",
            recordId: null,
            recipeId: null, // receta od
            recordItem: {},
            warehousesDetail: [],
            columns: {},
            item_unit_types: [],
            titleTopBar: '',
            title: '',
            showDialogHistory: false,
            showDialogItemStock: false,
            records:[],
            pagination:{},
            loading_submit:false,
            applyFilter: {
                type: Boolean,
                default: true,
                required: false
            },
            search:{},
            productType:{}
        };
    },
    created() {
        this.titleTopBar = 'Recetas y Costeos';
        this.title = 'Recetas y Costeos';
        
        this.$store.commit('setConfiguration', this.configuration);
        this.loadConfiguration()

        if (this.config.is_pharmacy !== true) {
            delete this.columns.sanitary;
            delete this.columns.cod_digemid;
        }
        if (this.config.show_extra_info_to_item !== true) {
            delete this.columns.extra_data;

        }
        this.$http.get(`/configurations/record`).then((response) => {
            this.$store.commit('setConfiguration', response.data.data);
            //this.config = response.data.data;
        });
        this.$eventHub.$on("reloadData", () => {
            this.getRecords();
        });
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
        }
    },
    async mounted() {
        await this.getRecords();
    },
    methods: {

        ...mapActions([
            'loadConfiguration',
        ]),
        
        changeClearInput() {
            this.search.value = "";
            this.getRecords();
        },
        getRecords() {
            this.loading_submit = true;
            return this.$http
                .get(`/${this.resource}/records?${this.getQueryParameters()}`)
                .then(response => {
                    this.records = this.parseValues(response.data.data);
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
                data[i].cif = JSON.parse(data[i].cif);
                data[i].costs = JSON.parse(data[i].costs);
            }
           console.log(data);
            return data ;
           } catch (e) {
            console.log(e);
           }
        },
        clickCreate(recordId = null) {
            if( typeof recordId == 'string' && recordId.indexOf("recipe") != -1){
                this.recordId = null; 
                this.recipeId = recordId;
            }
            else{
                this.recordId = recordId;
                this.recipeId = null; // recipe y sub recipe
            }
            this.showDialog = true;
        },
        clickDuplicate(recordId=null){
            this.recipeId = "duplicate"
            this.recordId = recordId
            this.showDialog = true;
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
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
        clickExportRecipes(idRecipe) {
            this.idRecipe = idRecipe;
            this.showExportRecipesDialog = true;
        },
    },
};
</script>

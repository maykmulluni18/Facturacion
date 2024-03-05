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
                                            Filtrar por:
                                        </div>
                                        <select v-model="search" class="form-control form-control-sm mb-1" >
                                            <option v-for="item in columns" :value="item"> {{ item.value }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 col-sm-12 pb-2">
                                    <div v-if="search.key == 'by_day'">
                                        <div class="d-flex">
                                            <el-date-picker v-model="search_by_day" type="date"
                                                value-format="yyyy-MM-dd" format="dd/MM/yyyy"
                                                :clearable="false">
                                            </el-date-picker>
                                        </div>
                                    </div>
                                    <div v-if="search.key == 'by_month'">
                                        <div class="d-flex">
                                            <el-date-picker v-model="search_by_month_start" type="month"
                                                value-format="yyyy-MM" format="MM/yyyy"
                                                :clearable="false">
                                            </el-date-picker>
                                        </div>
                                    </div>
                                    <div v-if="search.key == 'by_week'">
                                        <div class="btn-group mr-2" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-primary" v-for="key in button_group_week" @click.prevent="getOrdersDaysByWeek(key.week)">{{ key.week }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <div v-if="search.key == 'by_day'">
                                    <table class="table">
                                        <thead>
                                            <tr >
                                                <th v-for="item in days_table_by_day" style="background-color: lightskyblue;" class="text-center" > {{ item.name_day }} {{ item.date }} </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <div v-if="search.key == 'by_day'" v-for="record in records" >
                                                <div v-if="record.orders.length > 0" >
                                                    <tr v-for="(row , index) in record.orders" :key="index" style="background-color: lightskyblue;" >
                                                        <th>
                                                            <div style="background-color: aqua;" type="button">
                                                                Dueño del Pedido: {{ row.owner }}
                                                                <br>
                                                                Fecha de Entrega: {{ row.delivery_date }}
                                                                <br>
                                                                Referencia: {{ row.external_id }}
                                                            </div>
                                                        </th>
                                                    </tr>
                                                </div>
                                                <div v-else-if="record.id == number_current_day">
                                                    <tr>
                                                        <th>Hoy {{ record.name_day }} no hay pedidos</th>
                                                    </tr>
                                                </div>
                                            </div>
                                        </tbody>
                                    </table>
                                </div>
                                <div v-if="search.key == 'by_week'">
                                    <table class="table">
                                            <thead>
                                                <tr >
                                                    <th v-for="record in records" scope="col" class="text-center">{{ record.name_day }} {{ record.date }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td v-for="record in records">
                                                        <div v-for="key in record.orders" style="background-color: springgreen !important;">
                                                            <label for="" class="mb-0">Dueño: {{ key.owner }}</label>
                                                            <br>
                                                            <label for="" class="mb-0">Fecha de Entrega: {{ key.delivery_date }}</label>
                                                            <br>
                                                            <label for="" class="mb-0">Referencia:: {{ key.external_id }}</label>
                                                            <hr>
                                                        </div>

                                                    </td>
                                                </tr>
                                                
                                            </tbody>
                                    </table>
                                </div>
                                <div v-if="search.key == 'by_month'">
                                    <div class="container">
                                        <div class="row">
                                            <div type="button" v-for="month in tables_months" class="col-calendario" @click.prevent="getOrdersByMonth(month)">
                                                <div style="background-color: lightskyblue;padding: 1.35rem 0.75rem;text-align: center;border: 1px solid #e9e9e9;color: black;">
                                                    <div v-if="month.orders && month.orders.length > 0" style="background-color: springgreen !important;">
                                                         {{ month.name_day }}
                                                         <br>
                                                         Nº Pedidos {{ month.orders.length }}
                                                    </div>
                                                    <div v-else style="background-color: silver;"> 
                                                        {{ month.name_day }} 
                                                        <br>
                                                        Sin Pedidos
                                                    </div>
                                                     - {{ month.date }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
            <items-export :showDialog.sync="showExportDialog"></items-export>

            <!--
            : false,
            show_extra_info_to_item
            -->
            <tenant-item-aditional-info-modal
                :item="recordItem"
                :showDialog.sync="showDialogItemStock"
            ></tenant-item-aditional-info-modal>
            
        </div>
        <div v-if="showDialogOrders == true">
            <el-dialog :title="titleDialogOrders"
                    :visible="showDialogOrders"
                    width="35%"
                    @close="close">
                    <div v-for="key in records_orders_month ">
                            <label for="" class="mb-0">Dueño: {{ key.owner }}</label>
                            <br>
                            <label for="" class="mb-0">Fecha de Entrega: {{ key.delivery_date }}</label>
                            <br>
                            <label for="" class="mb-0">Referencia:: {{ key.external_id }}</label>
                            <hr>
                    </div>
            </el-dialog>
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
import DataTable from "../../../components/DataTable.vue";
import {deletable} from "../../../mixins/deletable";
import ItemsHistory from "@viewsModuleItem/items/history.vue";
import {mapActions, mapState} from "vuex";
import ItemsImportUpdatePrice from "./partials/update_prices.vue";
import queryString from "query-string";
import moment from 'moment';
import {functions, exchangeRate} from "@mixins/functions";



export default {
    props: [
        "configuration",
        "typeUser",
        "type",
        ],
    mixins: [deletable],
    components: {
        ItemsForm,
        ItemsImport,
        ItemsExport,
        ItemsExportWp,
        ItemsExportBarcode,
        ItemsExportExtra,
        DataTable,
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
            resource: "orderschedule",
            recordId: null,
            recordItem: {},
            warehousesDetail: [],
            item_unit_types: [],
            titleTopBar: '',
            records:[],
            pagination:{},
            loading_submit:false,
            title: '',
            showDialogHistory: false,
            showDialogItemStock: false,
            columns:[{key:"by_day",value:"Dia"},{key:"by_week",value:"Semana"},{key:"by_month",value:"Mes"}],
            days_table_by_day:[
                    {
                        id:0,
                        name_day: moment().day() == 0 ?"Domingo":moment().day() == 1?"Lunes":moment().day() ==2?"Martes":moment().day() == 3?"Miercoles":moment().day()== 4 ?"Jueves":moment().day()==5?"Viernes":"Sabado", 
                        orders:[],
                        date:moment().format('YYYY-MM-DD')
                    }
            ],
            applyFilter: {
                type: Boolean,
                default: true,
                required: false
            },
            tables_months:[],
            button_group_week:[{week:1,date:""},{week:2,date:""},{week:3,date:""},{week:4,date:""},{week:5,date:""},{week:6,date:""},{week:7,date:""},{week:8,date:""}],
            search_alternative_to:moment().format('YYYY-MM-DD'),
            number_current_day:moment().day(),
            search:{key:"by_day",value:"Dia"},
            search_by_day:null,
            search_by_month_start:null,
            search_by_week_start:null,
            search_by_week_end:null,
            showDialogOrders:false,
            titleDialogOrders:"Detalles de los Pedidos",
            records_orders_month:[]
        };
    },
    created() {
        this.title = 'Agenda de Pedidos - Calendario';
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
        this.search_by_day = moment().format('YYYY-MM-DD HH:mm:ss');
        this.canCreateProduct();
        this.$eventHub.$on("reloadData", () => {
            this.getRecords(this.search);
        });
        this.fillDateSearchByWeek();
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
        await this.getRecords({key:"by_day",value:"Dia"});
    },
    watch:{
        search(value){
            this.search = value;
            this.addInformationSecondSearch(value);
        },
        search_by_day(value){
            this.search_by_day =value;
            this.addInformationSecondSearch(value);
        },
        search_by_month_start(value){
            this.search_by_month_start=value;
            this.addInformationSecondSearch(value);
        }
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
                if (this.config !== undefined && this.config.seller_can_create_product !== undefined) {
                    this.can_add_new_product = this.config.seller_can_create_product;
                }
            }
            return this.can_add_new_product;
        },
        getRecords(filter) {
            this.loading_submit = true;
            let querys = "";
            if(this.search.key == 'by_day'){
                querys=querys + "date_day="+this.search_by_day;
            }else if(this.search.key == 'by_week'){
                if(!this.search_by_week_end || !this.search_by_week_start){
                    this.search_by_week_start = this.button_group_week[0].date;
                    this.search_by_week_end = moment(this.button_group_week[0].date).add(1,'weeks').format("YYYY-MM-DD HH:mm:ss");
                }
                querys=querys + "date_start_week="+this.search_by_week_start + "&date_end_week="+this.search_by_week_end ;
            }else if(this.search.key == 'by_month'){
                if(!this.search_by_month_start) this.search_by_month_start=moment().format("YYYY-MM")
                querys=querys+"date_month_start="+this.search_by_month_start;
            }
            querys=querys+"&by="+this.search.key;
            this.number_current_day = moment(this.search_alternative_to).format('d');
            return this.$http
            .get(`/${this.resource}/records?${this.getQueryParameters()}&${querys}`)
            .then(response => {
                if(filter.key == 'by_day') this.records = this.addOrdersToDay(response.data.data);
                else if(filter.key == 'by_week') this.records = this.addOrdersToWeek(response.data.data);
                else if(filter.key == 'by_month')  this.tables_months = this.addOrdersToMonth(response.data.data,this.search_by_month_start);
                by_month
            })
            .catch(error => {})
            .then(() => {
                this.loading_submit = false;
            });    
        },
        getOrdersByMonth(item){
            this.showDialogOrders = true;
            this.records_orders_month = item.orders
        },
        
        getNameDayByNumber(number){
            return number == 0 ?"Domingo":number == 1?"Lunes":number ==2?"Martes":number == 3?"Miercoles":number== 4 ?"Jueves":number==5?"Viernes":"Sabado" ;
        },
        addOrdersToDay(orders){
            this.days_table_by_day =[];
            let info_day = {} ;
            info_day.name_day = this.getNameDayByNumber(moment(this.search_by_day).format('d'));
            info_day.id = moment(this.search_by_day).format('d');
            info_day.date=this.search_by_day;
            this.days_table_by_day.push(info_day)
            return [{orders:orders,name_day:this.getNameDayByNumber(moment(this.search_by_day).format('d')),id:moment(this.search_by_day).format('d')}];
        },
        addOrdersToWeek(orders){
            let days_alternative = [];
            let day={};
            for (let i = 0; i < orders.length; i++) {
                orders[i].id_day = moment(orders[i].delivery_date).format('d');
            }
            for (let i = 0; i < 7; i++) {
                day.name_day = this.getNameDayByNumber(moment(moment().add(i,'days').format('YYYY-MM-DD')).format('d'));
                day.date = moment(this.search_by_week_start?this.search_by_week_start:moment().format("YYYY-MM-DD")).add(i,'days').format('YYYY-MM-DD');
                day.id = moment(this.search_by_week_start?this.search_by_week_start:moment().format("YYYY-MM-DD")).add(i,'days').format('d');
                day.orders = orders.filter(e=>e.id_day == day.id);
                days_alternative.push(day);
                day={}
            }
            return days_alternative ;
        },
        fillDateSearchByWeek(){
            let current_date = moment().format('YYYY-MM-DD HH:mm:ss');
            for (let i = 0; i < 8; i++) {
                this.button_group_week[i].date = moment(current_date).add(i,'weeks').format('YYYY-MM-DD HH:mm:ss');        
            }

        },
        addOrdersToMonth(orders,date_month_start){
            let amount_days_in_month = moment(date_month_start, "YYYY-MM").daysInMonth();
            let first_day_month = moment(date_month_start).format("YYYY-MM-01");
            let days_alternative = []; 
            for (let i = 0; i < orders.length; i++) {
                orders[i].date = moment(orders[i].delivery_date).format("YYYY-MM-DD");
            }
            let day = {};
            for (let i = 0; i < amount_days_in_month; i++) {
                day.id = moment(first_day_month).add(i,'days').format('d');
                day.name_day = this.getNameDayByNumber(day.id);
                day.date = moment(first_day_month).add(i,'days').format('YYYY-MM-DD');
                day.orders = orders.filter(e=>e.date == day.date);
                days_alternative.push(day);
                day={}
            }
            return days_alternative ;
        },
        close(){
            this.showDialogOrders =false
        },
        addInformationSecondSearch(params){
            if(this.search.key == 'by_day'){
                this.records = [] ;
                this.getRecords(this.search);
            }else if(this.search.key == 'by_week'){
                this.days_table_by_day = [];
                this.getRecords(this.search);
            }else if(this.search.key == 'by_month'){
                this.getRecords(this.search);
            }
        },
        getNameMonthById(id){
            return id == 0?"ENERO":id == 1?"FEBRERO":id == 2?"MARZO":id == 3?"ABRIL":id == 4?"MAYO":id == 5?"JUNIO":id == 6?"JULIO":id == 7?"AGOSTO":id == 8?"SEPTIEMBRE":id == 9?"OCTUBRE":id == 10?"NOVIEMBRE":"DICIEMBRE";
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
        
        clickCreate(recordId = null) {
            this.recordId = recordId;
            this.showDialog = true;
        },
        clickExport() {
            this.showExportDialog = true;
        },
        clickDelete(id) {
            this.destroy(`/${this.resource}/${id}`).then(() =>
                this.$eventHub.$emit("reloadData")
            );
        },
        getOrdersDaysByWeek(id_week){
            this.search_by_week_start =  this.button_group_week[id_week-1].date;
            this.search_by_week_end =  moment(this.button_group_week[id_week-1].date).add(1,'weeks').format('YYYY-MM-DD HH:mm:ss');
            this.getRecords(this.search);
        }
    },
};
</script>
<style>
    .col-calendario {
    -ms-flex: 0 0 14.285%;
    flex: 0 0 14.285%;
    max-width: 14.285%;
    }
</style>
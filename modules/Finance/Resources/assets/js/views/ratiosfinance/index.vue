<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">
                {{ currentTitle}}
            </h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <table class="table table-borderless">
                    <thead>
                        <tr>
                        <th scope="col"></th>
                        <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <div class="d-flex">
                            <el-date-picker v-model="search_by_month_start" type="month"
                                value-format="yyyy-MM-dd" format="yyyy-MM-dd"
                                :clearable="false">
                            </el-date-picker>
                            <button type="button" class="btn btn-primary" @click.prevent="getRecords()">Buscar</button>
                        </div>
                        <tr>
                            <td><h4 style="color: green;">Ratio Tesoreria Form</h4>  
                                <p style="font-size: 12x;">(Suma saldos todas las cuentas + Suma cuentas por cobrar menores a 30 días) / Cuentas por pagar menores a 30 días.</p> 
                            </td>
                            <td>{{ ratios_tesoreria }}</td>
                        </tr>
                        <tr>
                            <td><h4 style="color: green">Ratio de Liquidez</h4>
                                <p style="font-size: 12px;">(Suma de saldos de todas las cuentas + Suma de todas las Cuentas por cobrar + Inventarios valorizados en soles) / Suma de todas las cuentas por pagar</p> 
                            </td>
                            <td>{{ ratios_liquidez }}</td>
                        </tr>
                        <tr>
                            <td><h4 style="color: green">Ratio de Rentabilidad del Capital Total</h4>
                                <p style="font-size: 12px;">(Suma de saldos de todas las cuentas + Suma de Cuentas por Pagar) *100 </p> 
                            </td>
                            <td>{{ ratios_rentabilidad_capital_total }}</td>
                        </tr>

                        <div class="d-flex">
                              Desde:
                            <el-date-picker v-model="search_by_day_start" type="date"
                                value-format="yyyy-MM-dd" format="yyyy-MM-dd"
                                :clearable="false">
                            </el-date-picker>
                              Hasta:
                            <el-date-picker v-model="search_by_day_end" type="date"
                                value-format="yyyy-MM-dd" format="yyyy-MM-dd"
                                :clearable="false">
                            </el-date-picker>
                            <button type="button" class="btn btn-primary" @click.prevent="getInventoryValuedFinal()">Buscar</button>
                        </div>
                        <tr>
                            <td> <h4 style="color: green"> Ratio de Rentabilidad General</h4></td>
                            <td>{{ ratio_rentabilidad_general }}</td>
                        </tr>
                        <tr>
                            <td> <h4 style="color: green"> Ratio de Rentabilidad de Ventas </h4></td>
                            <td>{{ ratio_rentabilidad_ventas }}</td>
                        </tr>
                    </tbody>
                </table>
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
            title:'Ratios Financieros',
            resource: "finances/ratiosfinance",
            form: {},
            filter: {
                column: '',
                order: ''
            },
            ratios_tesoreria : 0 ,
            ratios_liquidez : 0 ,
            ratios_rentabilidad_capital_total:0,
            ratio_rentabilidad_ventas:0,
            ratio_rentabilidad_general:0,
            search_by_month_start:moment().format("YYYY-MM-DD"),
            search_by_day_start:moment().subtract(1,"months").format("YYYY-MM-DD"),
            search_by_day_end:moment().format("YYYY-MM-DD")

        };
    },computed:{
        ...mapState([
            'config',
        ]),
        showDestination:function(){
            return !(this.ismovements !== undefined && this.ismovements === 0);
        },
        currentTitle:function(){
            this.title = 'Ratios Financieros';
            return this.title
        }
    },
    async created() {
        if(this.ismovements === undefined) this.ismovements = 1
        this.ismovements = parseInt(this.ismovements)
        this.$store.commit('setConfiguration', this.configuration);
        await this.getRecords()
        await this.getInventoryValuedFinal()
        this.currentTitle
    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        ChangeOrder(col) {
            if (this.filter.order !== 'DESC') {
                this.filter.order = 'DESC'
            } else {
                this.filter.order = 'ASC'
            }
            this.filter.column = col
            this.$eventHub.$emit('filtrado', this.filter)
            console.log('sale')
        },
        getSumAccountsCobrarPagar(items){
            let sum = 0;
            for (let i = 0; i < items.length; i++) {
                let element = items[i];
                sum = sum + Number(element.total_to_pay)
            }
            return sum;
        },
        getSumAccountsBank(items){
            let sum = 0;
            for (let i = 0; i < items.length; i++) {
                let element = items[i];
                sum = sum + Number(element.balance)
            }
            return sum;
        },
        
        async getRecords(){
            // para los 3 primeros ratios
            // this.search_by_month_start; // esta en formato YYYY-MM-DD
            // ------------------

            // &date_end=2023-03-31&date_start=2023-03-01&establishment_id=1&month_end=2023-03&month_start=2023-03&page=1&payment_method_type_id=&period=between_dates&user_id=
            let resp_cobrar = await this.$http.get(`/finances/ratiosfinance/recordsCuentasPorCobrar?customer_id=&date_end=${this.search_by_day_end}&date_start=${moment(this.search_by_month_start).subtract(1,"months").format("YYYY-MM-DD")}&establishment_id=1&period=between_dates`);
            let sum_cobrar = this.getSumAccountsCobrarPagar(resp_cobrar.data.data);
            let params = { establishment_id:0, period:"between_dates", date_start:moment(this.search_by_month_start).subtract(1,"months").format("YYYY-MM-DD"), date_end:this.search_by_day_end, month_start:moment(this.search_by_month_start).subtract(1,"months").format("YYYY-MM-DD"), month_end:this.search_by_month_start,supplier_id:null,user:null};   
            let resp_pagar = await this.$http.post(`/finances/ratiosfinance/cuentasPorPagar`,params)
            let sum_to_pay = this.getSumAccountsCobrarPagar(resp_pagar.data.records);
            let resp_all_accounts = await this.$http.get(`/finances/ratiosfinance/sumaSaldoTodasCuentas`)
            let sum_bank_all = this.getSumAccountsBank(resp_all_accounts.data.records)
            this.ratios_tesoreria = sum_to_pay != 0 ? (sum_bank_all + sum_cobrar)/sum_to_pay:0 ;
            // para obtener la suma de todas las cuentas sin fechas
            let resp_cobrar_alls = await this.$http.get(`/finances/ratiosfinance/recordsCuentasPorCobrar?customer_id=&date_end=${this.search_by_day_end}&date_start=${moment(this.search_by_month_start).subtract(1,"months").format("YYYY-MM-DD")}&establishment_id=1`);
            let sum_all_accounts_cobrar = this.getSumAccountsCobrarPagar(resp_cobrar_alls.data.data);
            let params_ = { establishment_id:0,period:"any", date_start:moment(this.search_by_month_start).subtract(1,"months").format("YYYY-MM-DD"),date_end:this.search_by_day_end,month_start:moment(this.search_by_month_start).subtract(1,"months").format("YYYY-MM-DD"),month_end:this.search_by_month_start,supplier_id:null,user:null};
            let resp_pagar_alls = await this.$http.post(`/finances/to-pay/records`,params_)
            let sum_accounts_to_pay_all = this.getSumAccountsCobrarPagar(resp_pagar_alls.data.records);
            //   Inventarios valorizados en soles - consultar los insumos 
            let resp_invetory_value_soles = await this.$http.get(`/finances/ratiosfinance/recordsinventoryvalued`) ;
            this.ratios_liquidez = sum_accounts_to_pay_all != 0 ? (sum_bank_all + sum_all_accounts_cobrar + resp_invetory_value_soles.data.data ) / sum_accounts_to_pay_all:0;
            this.ratios_rentabilidad_capital_total =( sum_bank_all  + sum_accounts_to_pay_all) * 100
        },
        async getInventoryValuedFinal(){
             // para los 2 ultimos
            // this.search_by_day_start; // aca tiene la resta de un mes, y esta en formato YYYY-MM-DD
            // this.search_by_day_end; // esta en formato YYYY-MM-DD
            // let d_start = moment().format("YYYY-MM-DD");
            let query = "date_start="+this.search_by_day_start + "&date_end=" + this.search_by_day_end ;
            let resp_inventory_value_soles = await this.$http.get(`/finances/ratiosfinance/inventoryvaluedfinal?${query}`) ;
            this.ratio_rentabilidad_general =resp_inventory_value_soles.data.data;
            this.ratio_rentabilidad_ventas = resp_inventory_value_soles.data.totals_sales * 100
        }

        // http://test.localhost:8000/finances/to-pay/records post
        /* 
        {
        "establishment_id":0,
        "period":"between_dates",
        "date_start":"2023-03-01",
        "date_end":"2023-03-31",
        "month_start":"2023-03",
        "month_end":"2023-03",
        "supplier_id":null,
        "user":null}
        */
    }
};
</script>

<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">
                {{ currentTitle }}
            </h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <div class="container">
                    <div class="row">
                        <label for="">Desde: </label>
                        <div class="col">
                            <div class="d-flex">
                                <el-date-picker v-model="form.from" type="date" value-format="yyyy-MM-dd"
                                    format="dd/MM/yyyy" :clearable="false">
                                </el-date-picker>
                            </div>
                        </div>
                        <label for="">Hasta: </label>
                        <div class="col">
                            <div class="d-flex">
                                <el-date-picker v-model="form.to" type="date" value-format="yyyy-MM-dd" format="dd/MM/yyyy"
                                    :clearable="false">
                                </el-date-picker>
                            </div>
                        </div>
                        <div class="col">
                            <div class="btn-group mr-2" role="group" aria-label="Basic example">
                                <button type="button" class="btn btn-primary" @click.prevent="search()">Buscar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container">
                    <hr>
                    <div class="row">
                        
                        <div class="col">
                            <div class="row no-gutters" style="color:green;font-size: 15px;">
                                <div class="col-12 col-sm-6 col-md-8">Saldo Inicial </div>
                                <div class="col-6 col-md-4">S/ {{ balance_init }}</div>
                            </div>
                            <div v-for="row in columns_rigth">
                                <div class="row no-gutters" style="color:green">
                                    <div class="col-12 col-sm-6 col-md-8">{{ row.description }}</div>
                                    <div class="col-6 col-md-4">S/ {{ row.amount }}</div>
                                </div>
                                <div v-for="row_ in row.data" class="row no-gutters" style="color: black;">
                                    <div class="col-md-7 offset-md-1">{{ row_.name }}</div>
                                    <div class="col-6 col-md-4">S/ {{ row_.amount }}</div>
                                </div>
                                <hr>
                            </div>
                        </div>
                        <div class="col">
                            <div v-for="row in columns_left">
                                <div class="row no-gutters" style="color:red">
                                    <div class="col-12 col-sm-6 col-md-8">{{ row.description }}</div>
                                    <div class="col-6 col-md-4">S/ {{ row.amount }}</div>
                                </div>
                                <div v-for="row_ in row.items">
                                    <div class="row no-gutters">
                                        <div class="col-12 col-sm-6 col-md-8" style="color: green;"> {{ row_.description }}
                                        </div>
                                        <div class="col-6 col-md-4"> S/ {{ row_.amount }}</div>
                                    </div>
                                    <div v-for="row__ in row_.data" class="row no-gutters" style="color: black;">
                                        <div class="col-md-7 offset-md-1">{{ row__.name }}</div>
                                        <div class="col-6 col-md-4">S/ {{ row__.amount }}</div>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="row no-gutters">
                                    <div class="col-12 col-sm-6 col-md-8" style="color: green;"> Flujo de Caja Neto
                                    </div>
                                    <div class="col-6 col-md-4"> S/ {{ flujo_caja_neto }}</div>
                                </div>
                        </div>
                        
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
import DataTable from "../../components/DataTableFlowFinance.vue";
import { mapActions, mapState } from "vuex/dist/vuex.mjs";
import moment from "moment";
import queryString from 'query-string'


export default {
    components: {
        DataTable
    },
    props: [
        'configuration',
        'ismovements',
    ],
    data() {
        return {
            title: 'Flujo Financiero ',
            resource: "finances/flowfinance",
            records: [],
            balance_init : 0,
            venta_productos:0,
            columns_left: [
                {
                    description: "Margen de Contribucion Bruto",
                    amount: 0,
                    items: [
                        {
                            description: "Gastos Operativos Fijos",
                            amount: 0,
                            data: [
                                {
                                    name: "Alquiler de Planta",
                                    amount: 0
                                },
                                {
                                    name: "Agua - Planta",
                                    amount: 0
                                },
                                {
                                    name: "Luz - planta",
                                    amount: 0
                                },
                                {
                                    name: "Mano de Obra Directa Fija",
                                    amount: 0
                                },
                                {
                                    name: "Supervisor de Planta",
                                    amount: 0
                                },
                                {
                                    name: "Plan Movil",
                                    amount: 0
                                },
                                {
                                    name: "Internet",
                                    amount: 0
                                },
                                {
                                    name: "Seguros de Planta",
                                    amount: 0
                                },
                                {
                                    name: "Mercaderia de Empaque",
                                    amount: 0
                                }
                            ]
                        },
                        {
                            description: "Gastos Operativos Variables",
                            amount: 0,
                            data: [
                                {
                                    name: "Viáticos por viaje",
                                    amount: 0
                                },
                                {
                                    name: "CAJA CHICA",
                                    amount: 0
                                },
                                {
                                    name: "Transporte de Compras",
                                    amount: 0
                                },
                                {
                                    name: "Transporte Delivery",
                                    amount: 0
                                },
                                {
                                    name: "Mano de Obra Tercerizada",
                                    amount: 0
                                },

                            ]
                        },
                    ]
                },
                {
                    description: "Margen de Contribucion Operativo",
                    amount: 0,
                    items: [

                        {
                            description: "Gastos de Venta",
                            amount: 0,
                            data: [
                                {
                                    name: "Alquiler de Local Comercial",
                                    amount: 0
                                },
                                {
                                    name: "Planilla Comercial",
                                    amount: 0
                                },
                                {
                                    name: "Gastos de Representacion",
                                    amount: 0
                                },
                                {
                                    name: "Movilidad - Venta",
                                    amount: 0
                                },
                                {
                                    name: "Comisiones - Venta",
                                    amount: 0
                                },
                                {
                                    name: "Publicidad",
                                    amount: 0
                                },


                            ]
                        },
                        {
                            description: "Gastos de Administracion",
                            amount: 0,
                            data: [
                                {
                                    name: "Alquiler de Local Administrativo",
                                    amount: 0
                                },
                                {
                                    name: "Plan Movil",
                                    amount: 0
                                },
                                {
                                    name: "Agua - Oficinas",
                                    amount: 0
                                },
                                {
                                    name: "Luz - Oficinas",
                                    amount: 0
                                },
                                {
                                    name: "Utiles de Aseo y Oficina",
                                    amount: 0
                                },
                                {
                                    name: "Movilidad Administrativa",
                                    amount: 0
                                },
                                {
                                    name: "Gerente",
                                    amount: 0
                                },
                                {
                                    name: "Administrador",
                                    amount: 0
                                },
                                {
                                    name: "Auxiliar Administrativo",
                                    amount: 0
                                },
                                {
                                    name: "Jefe de Operaciones",
                                    amount: 0
                                },
                                {
                                    name: "Supervisores de Areas",
                                    amount: 0
                                },
                                {
                                    name: "Supervisor de Mantenimiento",
                                    amount: 0
                                },
                                {
                                    name: "Contador",
                                    amount: 0
                                },
                                {
                                    name: "Asesor Externo",
                                    amount: 0
                                },
                            ]
                        },
                        {
                            description: "Inversion al Activo",
                            amount: 0,
                            data: [
                                {
                                    name: "Compra de Equipos y Maquinarias",
                                    amount: 0
                                },
                                {
                                    name: "Implementacion o Arreglos",
                                    amount: 0
                                }
                            ]
                        },
                    ]
                },
                {
                    description: "Flujo de Caja Economico",
                    amount: 0,
                    items: [
                        {
                            description: "Impuestos",
                            amount: 0,
                            data: [
                                {
                                    name: "Beneficios Sociales",
                                    amount: 0
                                },
                                {
                                    name: "Renta 4ta Categoria",
                                    amount: 0
                                },
                                {
                                    name: "Renta 5ta Categoria",
                                    amount: 0
                                },
                                {
                                    name: "IGV",
                                    amount: 0
                                },
                                {
                                    name: "Detracciones",
                                    amount: 0
                                },
                                {
                                    name: "Arbitrios",
                                    amount: 0
                                },
                                {
                                    name: "Impuesto Predial",
                                    amount: 0
                                },
                            ]
                        },
                        {
                            description: "Gastos Financieros",
                            amount: 0,
                            data: [
                                {
                                    name: "Comis. Mante. Ctas. de Banco",
                                    amount: 0
                                },
                                {
                                    name: "Cuota de prestamos",
                                    amount: 0
                                },
                                {
                                    name: "Comis. Pasarelas de Pago",
                                    amount: 0
                                },
                            ]
                        },
                        {
                            description: "Gastos de Back Office",
                            amount: 0,
                            data: []
                        },
                    ]
                }
            ],
            
            
            columns_rigth: [
                {
                    description: "Ingresos Operativos",
                    amount: 0,
                    data: [

                        {
                            name: "Venta de Productos",
                            amount: 0
                        },
                        {
                            name: "Venta de Servicios",
                            amount: 0
                        },
                        {
                            name: "Otro tipo de Ingresos",
                            amount: 0
                        }
                    ]
                },
                {
                    description: "Costo Directo Bruto",
                    amount: 0,
                    data: [
                        {
                            name: "Mercaderia de Produccion",
                            amount: 0
                        }

                    ]
                }
            ],
            columns_temp: [],
            form: { to: moment().format("YYYY-MM-DD"), from: moment(moment().format("YYYY-MM-DD")).subtract(1, "month").format("YYYY-MM-DD") },
            totals:{},
            pago_prestamos_bancarios:0,
            costo_directo_bruto:0,
            flujo_caja_neto:0
        };
    }, computed: {
        ...mapState([
            'config',
        ]),
        showDestination: function () {
            return !(this.ismovements !== undefined && this.ismovements === 0);
        },
        currentTitle: function () {
            this.title = 'Flujo Financiero ';
            return this.title
        }
    },
    async created() {
        this.columns_left = this.getColumnsLeft()
        this.columns_rigth = this.getColumnsRigth()
        this.loadConfiguration()
        this.currentTitle
        await this.getAmountIngresos()
        await this.getRecordsInitBalance()
        await this.getRecords()
        await this.getRecordsRigth()
        await this.getRecordsLeft()
        this.columns_left[0].amount = this.balance_init + this.columns_rigth[0].data[0].amount ;
        this.columns_left[0].amount = this.columns_left[0].amount  - this.columns_rigth[1].data[0].amount ;
        this.columns_left[1].amount = this.columns_left[0].amount - (this.columns_left[0].items[0].amount + this.columns_left[0].items[1].amount)
        this.columns_left[2].amount = this.columns_left[1].amount - (this.columns_left[1].items[0].amount + this.columns_left[1].items[1].amount + this.columns_left[1].items[2].amount)
        this.flujo_caja_neto = this.columns_left[2].amount - (this.columns_left[2].items[0].amount + this.columns_left[2].items[1].amount + this.columns_left[2].items[2].amount)

    },
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        getColumnsLeft(){
            return [
                {
                    description: "Margen de Contribucion Bruto",
                    amount: 0,
                    items: [
                        {
                            description: "Gastos Operativos Fijos",
                            amount: 0,
                            data: [
                                {
                                    name: "Alquiler de Planta",
                                    amount: 0
                                },
                                {
                                    name: "Agua - Planta",
                                    amount: 0
                                },
                                {
                                    name: "Luz - planta",
                                    amount: 0
                                },
                                {
                                    name: "Mano de Obra Directa Fija",
                                    amount: 0
                                },
                                {
                                    name: "Supervisor de Planta",
                                    amount: 0
                                },
                                {
                                    name: "Plan Movil",
                                    amount: 0
                                },
                                {
                                    name: "Internet",
                                    amount: 0
                                },
                                {
                                    name: "Seguros de Planta",
                                    amount: 0
                                },
                                {
                                    name: "Mercaderia de Empaque",
                                    amount: 0
                                }
                            ]
                        },
                        {
                            description: "Gastos Operativos Variables",
                            amount: 0,
                            data: [
                                {
                                    name: "Viáticos por viaje",
                                    amount: 0
                                },
                                {
                                    name: "CAJA CHICA",
                                    amount: 0
                                },
                                {
                                    name: "Transporte de Compras",
                                    amount: 0
                                },
                                {
                                    name: "Transporte Delivery",
                                    amount: 0
                                },
                                {
                                    name: "Mano de Obra Tercerizada",
                                    amount: 0
                                },

                            ]
                        },
                    ]
                },
                {
                    description: "Margen de Contribucion Operativo",
                    amount: 0,
                    items: [

                        {
                            description: "Gastos de Venta",
                            amount: 0,
                            data: [
                                {
                                    name: "Alquiler de Local Comercial",
                                    amount: 0
                                },
                                {
                                    name: "Planilla Comercial",
                                    amount: 0
                                },
                                {
                                    name: "Gastos de Representacion",
                                    amount: 0
                                },
                                {
                                    name: "Movilidad - Venta",
                                    amount: 0
                                },
                                {
                                    name: "Comisiones - Venta",
                                    amount: 0
                                },
                                {
                                    name: "Publicidad",
                                    amount: 0
                                },


                            ]
                        },
                        {
                            description: "Gastos de Administracion",
                            amount: 0,
                            data: [
                                {
                                    name: "Alquiler de Local Administrativo",
                                    amount: 0
                                },
                                {
                                    name: "Plan Movil",
                                    amount: 0
                                },
                                {
                                    name: "Agua - Oficinas",
                                    amount: 0
                                },
                                {
                                    name: "Luz - Oficinas",
                                    amount: 0
                                },
                                {
                                    name: "Utiles de Aseo y Oficina",
                                    amount: 0
                                },
                                {
                                    name: "Movilidad Administrativa",
                                    amount: 0
                                },
                                {
                                    name: "Gerente",
                                    amount: 0
                                },
                                {
                                    name: "Administrador",
                                    amount: 0
                                },
                                {
                                    name: "Auxiliar Administrativo",
                                    amount: 0
                                },
                                {
                                    name: "Jefe de Operaciones",
                                    amount: 0
                                },
                                {
                                    name: "Supervisores de Areas",
                                    amount: 0
                                },
                                {
                                    name: "Supervisor de Mantenimiento",
                                    amount: 0
                                },
                                {
                                    name: "Contador",
                                    amount: 0
                                },
                                {
                                    name: "Asesor Externo",
                                    amount: 0
                                },
                            ]
                        },
                        {
                            description: "Inversion al Activo",
                            amount: 0,
                            data: [
                                {
                                    name: "Compra de Equipos y Maquinarias",
                                    amount: 0
                                },
                                {
                                    name: "Implementacion o Arreglos",
                                    amount: 0
                                }
                            ]
                        },
                    ]
                },
                {
                    description: "Flujo de Caja Economico",
                    amount: 0,
                    items: [
                        {
                            description: "Impuestos",
                            amount: 0,
                            data: [
                                {
                                    name: "Beneficios Sociales",
                                    amount: 0
                                },
                                {
                                    name: "Renta 4ta Categoria",
                                    amount: 0
                                },
                                {
                                    name: "Renta 5ta Categoria",
                                    amount: 0
                                },
                                {
                                    name: "IGV",
                                    amount: 0
                                },
                                {
                                    name: "Detracciones",
                                    amount: 0
                                },
                                {
                                    name: "Arbitrios",
                                    amount: 0
                                },
                                {
                                    name: "Impuesto Predial",
                                    amount: 0
                                },
                            ]
                        },
                        {
                            description: "Gastos Financieros",
                            amount: 0,
                            data: [
                                {
                                    name: "Comis. Mante. Ctas. de Banco",
                                    amount: 0
                                },
                                {
                                    name: "Cuota de prestamos",
                                    amount: 0
                                },
                                {
                                    name: "Comis. Pasarelas de Pago",
                                    amount: 0
                                },
                            ]
                        },
                        {
                            description: "Gastos de Back Office",
                            amount: 0,
                            data: []
                        },
                    ]
                }
            ];
        },
        getColumnsRigth(){
            return [
                {
                    description: "Ingresos Operativos",
                    amount: 0,
                    data: [

                        {
                            name: "Venta de Productos",
                            amount: 0
                        },
                        {
                            name: "Venta de Servicios",
                            amount: 0
                        },
                        {
                            name: "Otro tipo de Ingresos",
                            amount: 0
                        }
                    ]
                },
                {
                    description: "Costo Directo Bruto",
                    amount: 0,
                    data: [
                        {
                            name: "Mercaderia de Produccion",
                            amount: 0
                        }

                    ]
                }
            ]
        },
        async search(){
            this.columns_left = this.getColumnsLeft()
            this.columns_rigth = this.getColumnsRigth()
            await this.getAmountIngresos()
            await this.getRecordsInitBalance()
            await this.getRecords()
            await this.getRecordsRigth()
            await this.getRecordsLeft()
            this.columns_left[0].amount = this.balance_init + this.columns_rigth[0].data[0].amount ;
            this.columns_left[0].amount = this.columns_left[0].amount  - this.columns_rigth[1].data[0].amount ;
            this.columns_left[1].amount = this.columns_left[0].amount - (this.columns_left[0].items[0].amount + this.columns_left[0].items[1].amount)
            this.columns_left[2].amount = this.columns_left[1].amount - (this.columns_left[1].items[0].amount + this.columns_left[1].items[1].amount + this.columns_left[1].items[2].amount)
            this.flujo_caja_neto = this.columns_left[2].amount - (this.columns_left[2].items[0].amount + this.columns_left[2].items[1].amount + this.columns_left[2].items[2].amount)
        },
        async getRecords() {
            
            let query = "date_start=" + this.form.from + "&date_end=" + this.form.to;
            this.$http.get(`/expenses/records/flowfinance?${query}`)
                .then(response => {
                    
                    this.getTotalAmount(response.data.data); // columns left
                })
        },
        async getRecordsInitBalance() {
            
            let query = "date_start=" + this.form.from + "&date_end=" + this.form.to;
            this.$http.get(`/expenses/records/currentbalance?${query}`)
                .then(response => { this.balance_init = response.data.saldo_inicial })
        },
        async getAmountIngresos() {
            // http://test5.localhost:8000/finances/global-payments/records?date_end=2023-04-01&date_start=2023-04-01&destination_type=&month_end=2023-04&month_start=2023-03&page=1&payment_type=&period=month
            let query = "date_start=" + this.form.from + "&date_end=" + this.form.to + "&destination_type=&month_end=&month_start=&page=1&payment_type=&period=between_dates";
            this.$http.get(`/finances/global-payments/records?${query}`)
                .then(response => {
                    let ingresos = response.data.data.filter(e=>e.instance_type_description == "CPE" || e.instance_type_description == "NOTA DE VENTA")
                    let ingresos_2 = response.data.data.filter(e=> e.instance_type_description == 'INGRESO' && !e.number_full.includes("Saldo inicial -"));
                    this.pago_prestamos_bancarios =  this.sumaIngresos(response.data.data.filter(e=>e.instance_type_description == "PAGO PRESTAMO BANCARIO"));
                    this.costo_directo_bruto =  this.sumaIngresos(response.data.data.filter(e=>e.instance_type_description == "COMPRA"));
                    this.columns_rigth[1].data[0].amount = this.costo_directo_bruto
                    this.columns_rigth[1].amount = this.costo_directo_bruto
                    let total_ingresos = this.sumaIngresos(ingresos.concat(ingresos_2))
                    this.columns_rigth[0].data[0].amount = total_ingresos
                    
                })
        },
        sumaIngresos(inputs){
            let total = 0
            for (let i = 0; i < inputs.length; i++) {
                total = total + Number(inputs[i].total)
            }
            return total;
        },
        getTotalAmount(data) {
            for (let j = 0; j < this.columns_left.length; j++) {
                for (let k = 0; k < this.columns_left[j].items.length; k++) {
                    for (let l = 0; l < this.columns_left[j].items[k].data.length; l++) {
                        let item = this.columns_left[j].items[k].data[l];
                        let index = data.findIndex(e => e.expense_reason_description == item.name)
                        if (index != -1) this.columns_left[j].items[k].data[l].amount = this.columns_left[j].items[k].data[l].amount + data[index].total;
                        else this.columns_left[j].items[k].data[l].amount = 0;
                    }
                }
            }
            this.columns_left[2].items[1].data[1].amount = this.pago_prestamos_bancarios

            for (let j = 0; j < this.columns_left.length; j++) {
                for (let k = 0; k < this.columns_left[j].items.length; k++) {
                    let total = 0;
                    for (let l = 0; l < this.columns_left[j].items[k].data.length; l++) {
                        total = total + this.columns_left[j].items[k].data[l].amount
                    }
                    this.columns_left[j].items[k].amount = total;
                }
            }
        },
        async getRecordsRigth() {
            let query = "date_start=";
            query = query + this.form.from + "&date_end=" + this.form.to;
            this.$http.get(`/expenses/records/flowfinancerigth?column=date_of_issue&page=1&series=&total_canceled=&value=`)
                .then(response => {
                    // this.getTotalAmountRigth(response.data.data); // columns rigth
                })
        },
        async getRecordsLeft() {
            return this.$http.get(`/documents/records?${this.getQueryParameters()}`).then((response) => {
                this.records = response.data.data
                // this.initTotals()
            });
        },
        getQueryParameters() {
            return queryString.stringify({
                page: '',
                limit: '',
                ...''
            })
        },
    }
};
</script>

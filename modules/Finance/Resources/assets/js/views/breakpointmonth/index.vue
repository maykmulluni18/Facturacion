<template>
    <div class="card mb-0 pt-2 pt-md-0">
        <div class="card-header bg-info">
            <h3 class="my-0">
                {{ currentTitle }}
            </h3>
        </div>
        <div class="card mb-0">
            <div class="card-body">
                <data-table
                    :configuration="config"
                    :filter="filter"
                    :ismovements="ismovements"
                    :resource="resource">
                    <tr slot="heading">
                        <th class="">#</th>
                        <th :class="(filter.column === 'date_of_payment')?'text-info':''"
                            @click="ChangeOrder('date_of_payment')">
                            Fecha
                        </th>
                        <th :class="(filter.column === 'person_name')?'text-info':''"
                            @click="ChangeOrder('person_name')">
                            Adquiriente
                        </th>
                    </tr>
                    <tr slot-scope="{ index, row }">
                        <!-- # -->
                        <td>{{ row.index }}</td>
                        <!-- Fecha -->
                        <td>{{ row.date_of_payment }}</td>
                        <!-- Adquiriente -->
                        <td>
                            {{ row.person_name }}<br/><small
                            v-text="row.person_number"
                        ></small>
                        </td>
                    </tr>
                </data-table>
            </div>
        </div>
    </div>
</template>

<script>
import DataTable from "../../components/DataTableBreakPointMonth.vue";
import {mapActions, mapState} from "vuex/dist/vuex.mjs";

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
            title:'Punto de Equilibrio - Mensual',
            resource: "finances/breakpointmonth",
            form: {},
            filter: {
                column: '',
                order: ''
            }
        };
    },computed:{
        ...mapState([
            'config',
        ]),
        showDestination:function(){
            return !(this.ismovements !== undefined && this.ismovements === 0);
        },
        currentTitle:function(){
            this.title = 'Punto de Equilibrio - Mensual';
            return this.title
        }
    },
    created() {
        if(this.ismovements === undefined) this.ismovements = 1
        this.ismovements = parseInt(this.ismovements)
        this.$store.commit('setConfiguration', this.configuration);
        this.loadConfiguration()
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
        }
    }
};
</script>

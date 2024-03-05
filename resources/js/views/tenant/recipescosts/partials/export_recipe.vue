<template>
    <el-dialog title="Exportar Receta" :visible="showDialog" @close="close" class="dialog-import">
        <form autocomplete="off" @submit.prevent="submit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-8">
                        <label class="control-label">Se va Exportar a PDF la Receta </label>
                    </div>
                    

                </div>
                <div class="form-actions text-right mt-4">
                    <el-button @click.prevent="close()">Cancelar</el-button>
                    <el-button type="primary" native-type="submit" :loading="loading_submit">Procesar</el-button>
                </div>
            </div>
        </form>
    </el-dialog>
</template>

<script>
    import queryString from 'query-string'

    export default {
        props: [
            'showDialog',
            'pharmacy',
            "idRecipe"
        ],
        data() {
            return {
                loading_submit: false,
                headers: headers_token,
                resource: 'recipescosts',
                errors: {},
                form: {},
                fromPharmacy: false,
            }
        },
        created() {
            
            if(this.pharmacy !== undefined && this.pharmacy === true){
                this.fromPharmacy = true;
            }
            this.initForm()
        },
        methods: {
            
            initForm() {
                this.errors = {}
                this.form = {
                    period: 'month',
                    month_start: moment().format('YYYY-MM'),
                    month_end: moment().format('YYYY-MM'),
                }
            },
            close() {
                this.$emit('update:showDialog', false)
                this.initForm()
            },
            changeDisabledMonths() {
                if (this.form.month_end < this.form.month_start) {
                    this.form.month_end = this.form.month_start
                }
            },
            changePeriod() {

                if(this.form.period === 'between_months') {
                    this.form.month_start = moment().startOf('year').format('YYYY-MM'); //'2019-01';
                    this.form.month_end = moment().endOf('year').format('YYYY-MM');;
                }

            },
            submit() {
                this.loading_submit = true

                let query = queryString.stringify({
                    isPharmacy:this.fromPharmacy,
                    ...this.idRecipe
                });
                window.open(`/${this.resource}/export2/?id=${this.idRecipe}`, '_blank');
                this.loading_submit = false
                this.$emit('update:showDialog', false)
                this.initForm()
            }
        }
    }
</script>

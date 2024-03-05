<?php

    namespace Modules\Finance\Models;

    use App\Models\Tenant\Bank;
    use App\Models\Tenant\Cash;
    use App\Models\Tenant\DocumentPayment;
    use App\Models\Tenant\ModelTenant;
    use App\Models\Tenant\PurchasePayment;
    use App\Models\Tenant\SaleNotePayment;
    use App\Models\Tenant\SoapType;
    use App\Models\Tenant\TransferAccountPayment;
    use App\Models\Tenant\User;
    use Carbon\Carbon;
    use Eloquent;
    use Exception;
    use Hyn\Tenancy\Traits\UsesTenantConnection;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\MorphTo;
    use Illuminate\Database\QueryException;
    use Illuminate\Support\HigherOrderCollectionProxy;
    use Modules\Expense\Models\BankLoan;
    use Modules\Expense\Models\BankLoanPayment;
    use Modules\Expense\Models\ExpensePayment;
    use Modules\Pos\Models\CashTransaction;
    use Modules\Sale\Models\ContractPayment;
    use Modules\Sale\Models\QuotationPayment;
    use Modules\Sale\Models\TechnicalServicePayment;

    /**
     * Modules\Finance\Models\GlobalPayment
     *
     * @property int                          $id
     * @property string                       $name
     * @property float                        $amount
     * @property Carbon                       $created_at
     * @property Carbon                       $updated_at
     * @property-read TechnicalServicePayment $tec_serv_payment
     * @method static Builder|GlobalPayment newModelQuery()
     * @method static Builder|GlobalPayment newQuery()
     * @method static Builder|GlobalPayment query()
     * @method static Builder|GlobalPayment whereDefinePaymentType($payment_type)
     * @method static Builder|GlobalPayment whereFilterPaymentType($params)
     * @mixin ModelTenant
     */
    class IndirectExpense extends ModelTenant
    {

        use UsesTenantConnection;
        protected $table = 'indirect_expenses';
        protected $fillable = [
            'name',
            'amount',
            'created_at',
            'updated_at',
        ];

    }

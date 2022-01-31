<?php

namespace DrH\Mpesa\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * DrH\Mpesa\Database\Entities\MpesaC2bCallback
 *
 * @property int $id
 * @property string $transaction_type
 * @property string $trans_id
 * @property string $trans_time
 * @property float $trans_amount
 * @property int $business_short_code
 * @property string $bill_ref_number
 * @property string|null $invoice_number
 * @property string|null $third_party_trans_id
 * @property float $org_account_balance
 * @property string $msisdn
 * @property string|null $first_name
 * @property string|null $middle_name
 * @property string|null $last_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read mixed $name
 *
 */
class MpesaC2bCallback extends Model
{
    protected $guarded = [];

    public function getNameAttribute(): string
    {
        return implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]));
    }
}

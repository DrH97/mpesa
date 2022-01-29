<?php

namespace DrH\Mpesa\Database\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * DrH\Mpesa\Database\Entities\MpesaB2cResultParameter
 *
 * @property int $id
 * @property int $response_id
 * @property float $transaction_amount
 * @property string $transaction_receipt
 * @property string $b2c_recipient_is_registered_customer
 * @property float $b2c_charges_paid_account_available_funds
 * @property string $receiver_party_public_name
 * @property float b2c_utility_account_available_funds
 * @property float b2c_working_account_available_funds
 * @property Carbon $transaction_completed_date_time
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @mixin \Eloquent
 */
class MpesaB2cResultParameter extends Model
{
    protected $guarded = [];
}

<?php

namespace DrH\Mpesa\Database\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * DrH\Mpesa\Database\Entities\MpesaStkRequest
 *
 * @property int $id
 * @property string $phone
 * @property float $amount
 * @property string $reference
 * @property string $description
 * @property string $status
 * @property string $merchant_request_id
 * @property string $checkout_request_id
 * @property int|null $relation_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read MpesaStkCallback $response
 * @mixin \Eloquent
 */
class MpesaStkRequest extends Model
{
    protected $guarded = [];

    public function response(): HasOne
    {
        return $this->hasOne(MpesaStkCallback::class, 'checkout_request_id', 'checkout_request_id');
    }
}

<?php

namespace DrH\Mpesa\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * DrH\Mpesa\Database\Entities\MpesaBulkPaymentRequest
 *
 * @property int $id
 * @property string $conversation_id
 * @property string $originator_conversation_id
 * @property float $amount
 * @property string $phone
 * @property string|null $remarks
 * @property string $command_id
 * @property int|null $relation_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read MpesaBulkPaymentResponse $response
 *
 */
class MpesaBulkPaymentRequest extends Model
{
    protected $guarded = [];

    public function response(): HasOne
    {
        return $this->hasOne(MpesaBulkPaymentResponse::class, 'conversation_id', 'conversation_id');
    }
}

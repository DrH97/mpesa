<?php

namespace DrH\Mpesa\Database\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * DrH\Mpesa\Database\Entities\MpesaBulkPaymentResponse
 *
 * @property int $id
 * @property int $result_type
 * @property int $result_code
 * @property string $result_desc
 * @property string $originator_conversation_id
 * @property string $conversation_id
 * @property string $transaction_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read MpesaBulkPaymentRequest $request
 * @property-read MpesaB2cResultParameter $resultParameter
 * @mixin \Eloquent
 */
class MpesaBulkPaymentResponse extends Model
{
    protected $guarded = [];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MpesaBulkPaymentRequest::class, 'conversation_id', 'conversation_id');
    }

    public function resultParameter(): HasOne
    {
        return $this->hasOne(MpesaB2cResultParameter::class, 'response_id');
    }
}

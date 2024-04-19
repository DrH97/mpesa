<?php

namespace DrH\Mpesa\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransactionStatusCallback extends Model
{
    protected $guarded = [];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MpesaTransactionStatusRequest::class, 'conversation_id', 'conversation_id');
    }
}

<?php

namespace DrH\Mpesa\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MpesaTransactionStatusRequest extends Model
{
    protected $guarded = [];

    public function response(): HasOne
    {
        return $this->hasOne(MpesaTransactionStatusCallback::class, 'conversation_id', 'conversation_id');
    }
}

<?php

namespace DrH\Mpesa\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MpesaB2bRequest extends Model
{
    protected $guarded = [];

    public function response(): HasOne
    {
        return $this->hasOne(MpesaB2bCallback::class, 'conversation_id', 'conversation_id');
    }
}

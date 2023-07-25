<?php

namespace DrH\Mpesa\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaB2bCallback extends Model
{
    protected $guarded = [];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MpesaB2bRequest::class, 'conversation_id', 'conversation_id');
    }
}

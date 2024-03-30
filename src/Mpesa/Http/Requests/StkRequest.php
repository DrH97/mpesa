<?php

namespace DrH\Mpesa\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use JetBrains\PhpStorm\ArrayShape;

class StkRequest extends FormRequest
{
    #[ArrayShape(['amount' => "string", 'phone' => "string", 'reference' => "string", 'description' => "string"])]
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'phone' => 'required',
            'reference' => 'required',
            'description' => 'required',
        ];
    }
}

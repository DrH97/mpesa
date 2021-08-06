<?php


namespace DrH\Mpesa\Library;


class MpesaAccount
{
    public function __construct(
        public bool $sandbox = false,
        public string $type = 'PAYBILL',
        public ?string $shortcode = null,
        public ?string $key = null,
        public ?string $secret = null,
        public ?string $passkey = null,
    ) {}
}
<?php

namespace DrH\Mpesa\Library;

class MpesaAccount
{
    public const TILL = "TILL";
    public const PAYBILL = "PAYBILL";

    /**
     * MpesaAccount constructor.
     *
     * @param bool $sandbox
     * @param string $type
     * @param string $shortcode
     * @param string $key
     * @param string $secret
     * @param string $passkey
     *
     */
    public function __construct(
        public string  $shortcode,
        public string  $key,
        public string  $secret,
        public string  $passkey,
        public ?string $partyB = null,
        public bool    $sandbox = false,
        public string  $type = self::PAYBILL,
    ) {
    }
}

<?php

namespace DrH\Mpesa\Library;

class MpesaAccount
{
    const TILL = "TILL";
    const PAYBILL = "PAYBILL";

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
        public string $shortcode,
        public string $key,
        public string $secret,
        public string $passkey,
        public bool   $sandbox = false,
        public string $type = self::PAYBILL,
    ) {
    }
}

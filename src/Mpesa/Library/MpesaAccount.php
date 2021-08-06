<?php


namespace DrH\Mpesa\Library;

/**
 * Class MpesaAccount
 *
 * @package DrH\Mpesa\Library
 */
class MpesaAccount
{
    /**
     * MpesaAccount constructor.
     *
     * @param bool $sandbox
     * @param string $type
     * @param ?string $shortcode
     * @param ?string $key
     * @param ?string $secret
     * @param ?string $passkey
     *
     */
    public function __construct(
        public bool $sandbox = false,
        public string $type = 'PAYBILL',
        public ?string $shortcode = null,
        public ?string $key = null,
        public ?string $secret = null,
        public ?string $passkey = null,
    ) {}
}
<?php

namespace DrH\Mpesa\Library;

/**
 * Class MpesaAccount
 *
 * @package DrH\Mpesa\Library
 */
class MpesaAccount
{
    public string $shortcode;
    public string $key;
    public string $secret;
    public string $passkey;
    public bool $sandbox = false;
    public string $type = 'PAYBILL';

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
        string $shortcode,
        string $key,
        string $secret,
        string $passkey,
        bool   $sandbox = false,
        string $type = 'PAYBILL'
    )
    {
        $this->shortcode = $shortcode;
        $this->key = $key;
        $this->secret = $secret;
        $this->passkey = $passkey;
        $this->sandbox = $sandbox;
        $this->type = $type;
    }
}

<?php

namespace DrH\Mpesa\Repositories;

use DrH\Mpesa\Exceptions\ClientException;
use Exception;
use function config;
use function is_file;
use function random_int;
use function strlen;

class Generator
{
    /**
     * Generate a random transaction number
     *
     * @return string
     * @throws Exception
     */
    public static function generateTransactionNumber(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 15; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @param string $initiatorPass
     * @return string
     * @throws ClientException
     */
    public static function computeSecurityCredential(string $initiatorPass): string
    {
        if (config('drh.mpesa.sandbox')) {
            $pubKeyFile = __DIR__ . '/../Support/sandbox.cer';
        } else {
            $pubKeyFile = __DIR__ . '/../Support/production.cer';
        }
        if (is_file($pubKeyFile)) {
            $pubKey = file_get_contents($pubKeyFile);
        } else {
            throw new ClientException('Please provide a valid public key file');
        }
        openssl_public_encrypt($initiatorPass, $encrypted, $pubKey, OPENSSL_PKCS1_PADDING);
        return base64_encode($encrypted);
    }
}

<?php

return [
    /*
     |------------------------------------------------------
     | Set sandbox mode
     | ------------------------------------------------------
     | Specify whether this is a test app or production app
     | Sandbox base url: https://sandbox.safaricom.co.ke
     | Production base url: https://api.safaricom.co.ke
     |
     */
    'sandbox' => env('MPESA_SANDBOX', true),
    /*
     |------------------------------------------------------
     | Set multi tenancy mode
     | ------------------------------------------------------
     | Specify whether to use library with multi tenancy support (i.e. support multiple paybills)
     |
     */
    'multi_tenancy' => env('MPESA_MULTI_TENANCY', false),
    /*
   |--------------------------------------------------------------------------
   | Cache credentials
   |--------------------------------------------------------------------------
   |
   | If you decide to cache credentials, they will be kept in your app cache
   | configuration for sometime. Reducing the need for many requests for
   | generating credentials
   |
   */
    'cache_credentials' => true,
    /*
   |--------------------------------------------------------------------------
   | C2B array
   |--------------------------------------------------------------------------
   |
   | If you are accepting payments enter application details and shortcode info
   |
   */
    'c2b' => [
        /*
         * Consumer Key from developer portal
         */
        'consumer_key' => env('MPESA_KEY', 'uIeJwZwAWTbrNxQ8GypnDWSPtKXRbGql'),
        /*
         * Consumer secret from developer portal
         */
        'consumer_secret' => env('MPESA_SECRET', '6084w7ANkYErHipt'),
        /*
         * HTTP callback method [POST,GET]
         */
        'callback_method' => 'POST',
        /*
         * Your receiving paybill or till number
         */
        'short_code' => env('MPESA_C2B_SHORTCODE', '174379'),
        /*
         * Transaction type based on shortcode business type
         */
        'transaction_type' => env('MPESA_C2B_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
        /*
         * Optional Till number if different from shortcode
         */
        'party_b' => env('MPESA_C2B_PARTY_B'),
        /*
         * Passkey , requested from mpesa
         */
        'passkey' => env('MPESA_C2B_PASS_KEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),
        /*
         * --------------------------------------------------------------------------------------
         * Callbacks:
         * ---------------------------------------------------------------------------------------
         * Please update your app url in .env file
         * Note: This package has already routes for handling this callback.
         * You should leave this values as they are unless you know what you are doing
         */
        /*
         * Stk callback URL
         */
        'stk_callback' => env('APP_URL') . '/payments/callbacks/stk-callback',
        /*
         * Data is sent to this URL for successful payment
         */
        'confirmation_url' => env('APP_URL') . '/payments/callbacks/c2b-confirmation',
        /*
         * Mpesa validation URL.
         * NOTE: You need to email MPESA to enable validation
         */
        'validation_url' => env('APP_URL') . '/payments/callbacks/c2b-validation',
    ],
    /*
      |--------------------------------------------------------------------------
      | B2C array
      |--------------------------------------------------------------------------
      |
      | If you are sending payments to customers or b2b
      |
      */
    'b2c' => [
        /*
         * Sending app consumer key
         */
        'consumer_key' => env('MPESA_B2C_KEY'),
        /*
         * Sending app consumer secret
         */
        'consumer_secret' => env('MPESA_B2C_SECRET'),
        /*
         * Shortcode sending funds
         */
        'short_code' => env('MPESA_B2C_SHORTCODE', '603021'),
        /*
        * This is the user initiating the transaction, usually from the Mpesa organization portal
        * Make sure this was the user who was used to 'GO LIVE'
        * https://org.ke.m-pesa.com/
        */
        'initiator' => env('MPESA_B2C_INITIATOR', 'apiop37'),
        /*
         * The user security credential.
         * Go to https://developer.safaricom.co.ke/test_credentials and paste your initiator password to generate
         * security credential
         */
        'security_credential' => 'GXiVXirQFaJvEFOQyn+VJ4Gp3Ccvpoq6aqzFiNgvH18UMU59Qxc+UTAX7Blzo6L0+tQG2wUJ1fKH4YlPagt' .
            'zDHT37796uu0NysS85uPjxZMjnbGhPNeHnhJLzwyrjppl8mZpnmVg4CaVrEdcriuyifKIiF1hmc0A/RnjBMzY6yevbIV0kAgrn5cDvCN' .
            '99O1rr1nl69GaVbP7a/6AWnRkVUldnalQmqQhfgLbOdxjGOVGU2arqjuvgQ6glo1uK9PUnp3UH2Vv66Lu99JglWyjlcWufZhJXUmFFB9' .
            'tfoKAX2URnPGi4PvvJ6OgJNdsJmTsevnG2c/KKOa45rzdvwrwKA==',
        /*
         * Notification URL for timeout
         */
        'timeout_url' => env('APP_URL') . '/payments/callbacks/timeout/',
        /**
         * Result URL
         */
        'result_url' => env('APP_URL') . '/payments/callbacks/result/',
    ],
];

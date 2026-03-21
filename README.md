# Mpesa Payments API

[![GitHub TestCI Workflow](https://github.com/DrH97/mpesa/actions/workflows/test.yml/badge.svg?branch=main)](https://github.com/DrH97/mpesa/actions/workflows/test.yml)
[![Github StyleCI Workflow](https://github.com/DrH97/mpesa/actions/workflows/styleci.yml/badge.svg?branch=main)](https://github.com/DrH97/mpesa/actions/workflows/styleci.yml)
[![codecov](https://codecov.io/gh/DrH97/mpesa/branch/main/graph/badge.svg?token=6b0d0ba1-c2c6-4077-8c3a-1f567eea88a0)](https://codecov.io/gh/DrH97/mpesa)

[![Latest Stable Version](http://poser.pugx.org/drh/mpesa/v)](https://packagist.org/packages/drh/mpesa)
[![Total Downloads](http://poser.pugx.org/drh/mpesa/downloads)](https://packagist.org/packages/drh/mpesa)
[![Latest Unstable Version](http://poser.pugx.org/drh/mpesa/v/unstable)](https://packagist.org/packages/drh/mpesa)
[![License](http://poser.pugx.org/drh/mpesa/license)](https://packagist.org/packages/drh/mpesa)
[![PHP Version Require](http://poser.pugx.org/drh/mpesa/require/php)](https://packagist.org/packages/drh/mpesa)

<hr>

A Laravel package for integrating with Safaricom's M-Pesa payment APIs. Supports STK Push, C2B, B2C, B2B payments, transaction status queries, and identity verification.

Inspired by Mobile Money Library by Agweria: [https://mobile-money.agweria.com](https://mobile-money.agweria.com)

## Requirements

- PHP 8.2+
- Laravel 8.x - 13.x

## Installation

```bash
composer require drh/mpesa
```

The package auto-discovers its service provider and facades. Publish the configuration file:

```bash
php artisan vendor:publish --provider="DrH\Mpesa\MpesaServiceProvider"
```

Run migrations to create the payment tracking tables:

```bash
php artisan migrate
```

## Configuration

Add these environment variables to your `.env` file:

```dotenv
# General
MPESA_SANDBOX=true

# C2B / STK Push
MPESA_KEY=your-consumer-key
MPESA_SECRET=your-consumer-secret
MPESA_C2B_SHORTCODE=174379
MPESA_C2B_PASS_KEY=your-passkey
MPESA_C2B_TRANSACTION_TYPE=CustomerPayBillOnline

# B2C (Business to Customer)
MPESA_B2C_KEY=your-b2c-key
MPESA_B2C_SECRET=your-b2c-secret
MPESA_B2C_SHORTCODE=603021
MPESA_B2C_INITIATOR=apiop37

# B2B (Business to Business)
MPESA_B2B_KEY=your-b2b-key
MPESA_B2B_SECRET=your-b2b-secret
MPESA_B2B_SHORTCODE=600584
MPESA_B2B_INITIATOR=apiop37
```

See the published `config/drh.mpesa.php` for all available options including callback URLs, retry settings, multi-tenancy, and logging.

## Usage

### STK Push (Lipa Na M-Pesa Online)

```php
use DrH\Mpesa\Facades\STK;

// Simple push
$result = STK::push(amount: 100, phone: '254712345678', ref: 'Order001', description: 'Payment for order');

// Fluent API
$result = STK::amount(100)
    ->from('254712345678')
    ->usingReference('Order001', 'Payment for order')
    ->push();

// Check STK transaction status
$status = STK::status($checkoutRequestId);
```

### C2B (Customer to Business)

Register your validation and confirmation URLs with M-Pesa:

```php
use DrH\Mpesa\Facades\Registrar;

Registrar::shortcode(174379)
    ->onConfirmation('https://example.com/payments/callbacks/c2b-confirmation')
    ->onValidation('https://example.com/payments/callbacks/c2b-validation')
    ->submit();
```

### B2C (Business to Customer)

```php
use DrH\Mpesa\Facades\B2C;

// Send payment
$result = B2C::send(number: '254712345678', amount: 500, remarks: 'Salary payment');

// Fluent API
$result = B2C::to('254712345678')
    ->amount(500)
    ->withRemarks('Salary payment')
    ->send();

// Check balance
$balance = B2C::balance();

// Check transaction status
$status = B2C::status($transactionId);
```

### B2B (Business to Business)

```php
use DrH\Mpesa\Facades\B2B;

// Send payment
$result = B2B::pay(
    type: 'BusinessPayBill',
    shortcode: '600000',
    amount: 1000,
    reference: 'INV001',
    phone: '254712345678'
);

// Check transaction status
$status = B2B::status($request);
```

### Identity Verification

```php
use DrH\Mpesa\Facades\Identity;

$result = Identity::validate('254712345678');
```

## Events

The package dispatches events for payment lifecycle hooks. Listen for these in your `EventServiceProvider` or via `Event::listen()`:

| Event | Description |
|---|---|
| `StkPushRequestedEvent` | STK push request initiated |
| `StkPushPaymentSuccessEvent` | STK push payment completed successfully |
| `StkPushPaymentFailedEvent` | STK push payment failed |
| `C2bConfirmationEvent` | C2B payment confirmed |
| `B2cPaymentSuccessEvent` | B2C payment completed successfully |
| `B2cPaymentFailedEvent` | B2C payment failed |
| `B2bPaymentSuccessEvent` | B2B payment completed successfully |
| `B2bPaymentFailedEvent` | B2B payment failed |
| `TransactionStatusSuccessEvent` | Transaction status query succeeded |
| `TransactionStatusFailedEvent` | Transaction status query failed |
| `QueueTimeoutEvent` | Queue timeout occurred |

All events are in the `DrH\Mpesa\Events` namespace.

## Artisan Commands

```bash
# Register C2B validation and confirmation URLs
php artisan mpesa:register_c2b_urls

# Check status of pending STK transactions
php artisan mpesa:query_stk_status

# Check status of pending B2C transactions
php artisan mpesa:b2c_transaction_status

# Check status of pending B2B transactions
php artisan mpesa:b2b_transaction_status
```

## Callback Routes

The package automatically registers callback routes under `/payments/callbacks/`:

- `POST /payments/callbacks/stk-callback` - STK push callback
- `POST /payments/callbacks/c2b-validation` - C2B validation
- `POST /payments/callbacks/c2b-confirmation` - C2B confirmation
- `POST /payments/callbacks/result/` - B2C/B2B result
- `POST /payments/callbacks/timeout/` - B2C/B2B timeout

A `pesa.cors` middleware alias is available for CORS handling on callback routes.

## Testing

```bash
composer run-script test
```

## License

MIT

<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
use function base64_encode;
use function config;
use function json_decode;
use function strtolower;

/**
 * Class Authenticator
 *
 * @package DrH\Mpesa\Library
 */
class Authenticator
{

    /**
     * @var string
     */
    protected $endpoint;
    /**
     * @var Core
     */
    protected $engine;
    /**
     * @var Authenticator
     */
    protected static $instance;
    /**
     * @var bool
     */
    public $alt = false;
    /**
     * @var string
     */
    private $credentials;

    /**
     * Authenticator constructor.
     *
     * @param  Core $core
     * @throws MpesaException
     */
    public function __construct(Core $core)
    {
        $this->engine = $core;
        $this->endpoint = EndpointsRepository::build('auth');
        self::$instance = $this;
    }

    /**
     * @param bool $bulk
     * @return string
     * @throws MpesaException
     * @throws GuzzleException
     */
    public function authenticate($bulk = false, MpesaAccount $account = null): ?string
    {
        if ($bulk) {
            $this->alt = true;
        }
        $this->generateCredentials($account);
        if (config('drh.mpesa.cache_credentials', false) && !empty($key = $this->getFromCache())) {
            return $key;
        }
        try {
            $response = $this->makeRequest();
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody());
                $this->saveCredentials($body);
                return $body->access_token;
            }
            throw new MpesaException($response->getReasonPhrase());
        } catch (RequestException $exception) {
            $message = $exception->getResponse() ?
                $exception->getResponse()->getReasonPhrase() :
                $exception->getMessage();

            throw $this->generateException($message);
        }
    }

    /**
     * @param $reason
     * @return MpesaException
     */
    private function generateException($reason): ?MpesaException
    {
        switch (strtolower($reason)) {
            case 'bad request: invalid credentials':
                return new MpesaException('Invalid consumer key and secret combination');
            default:
                return new MpesaException($reason);
        }
    }

    /**
     * @return $this
     * @throws MpesaException
     */
    private function generateCredentials(MpesaAccount $account = null): self
    {
        if (config('drh.mpesa.multi_tenancy', false) && !$account->sandbox) {
            if ($account->key == null || $account->secret == null) {
                throw $this->generateException("Multi Tenancy is enabled but key or secret is null.");
            }

            $key = $account->key;
            $secret = $account->secret;
        } else {
            $key = config('drh.mpesa.c2b.consumer_key');
            $secret = config('drh.mpesa.c2b.consumer_secret');
            if ($this->alt) {
                //lazy way to switch to a different app in case of bulk
                $key = config('drh.mpesa.b2c.consumer_key');
                $secret = config('drh.mpesa.b2c.consumer_secret');
            }
        }

        $this->credentials = base64_encode($key . ':' . $secret);
        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws GuzzleException
     */
    private function makeRequest(): ResponseInterface
    {
        return $this->engine->client->request(
            'GET',
            $this->endpoint,
            [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->credentials,
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    /**
     * @return mixed
     */
    private function getFromCache()
    {
        return Cache::get($this->credentials);
    }

    /**
     * Store the credentials in the cache.
     *
     * @param $credentials
     */
    private function saveCredentials($credentials)
    {
        Cache::put($this->credentials, $credentials->access_token, 30);
    }
}

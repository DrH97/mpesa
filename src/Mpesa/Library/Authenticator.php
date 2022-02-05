<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
use function base64_encode;
use function config;
use function json_decode;
use function strtolower;

class Authenticator
{
    protected string $endpoint;

    public bool $alt = false;

    private string $credentials;

    private int $trials = 3;

    /**
     * Authenticator constructor.
     *
     * @param Core $engine
     * @throws ClientException
     */
    public function __construct(protected Core $engine)
    {
        $this->endpoint = EndpointsRepository::build('auth');
    }

    /**
     * @param bool $bulk
     * @param MpesaAccount|null $account
     * @return string|null
     * @throws ClientException|ExternalServiceException
     * @throws GuzzleException
     */
    public function authenticate(bool $bulk = false, MpesaAccount $account = null): ?string
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
            $body = json_decode($response->getBody());
            $this->saveCredentials($body);
            return $body->access_token;
        } catch (\GuzzleHttp\Exception\ClientException|ServerException $exception) {
            mpesaLogError($exception->getMessage());
            throw $this->generateException($exception);
        } catch (ConnectException $exception) {
            mpesaLogError($exception->getMessage());
            mpesaLogInfo($this->trials . " auth trials left.");
            if ($this->trials > 0) {
                $this->trials--;
                sleep(.2);
                return $this->authenticate($bulk, $account);
            }
            throw new ExternalServiceException('Mpesa Server Auth Error');
        }
    }

    /**
     * @param string $reason
     * @return ClientException
     */
    private function generateException(string $reason): ClientException
    {
        return match (strtolower($reason)) {
            'bad request: invalid credentials' =>
            new ClientException('Invalid consumer key and secret combination'),
            default => new ClientException($reason),
        };
    }

    /**
     * @param MpesaAccount|null $account
     * @return void
     * @throws ClientException
     */
    private function generateCredentials(MpesaAccount $account = null): void
    {
        if (config('drh.mpesa.multi_tenancy', false) && ($account && !$account->sandbox)) {
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
    private function getFromCache(): mixed
    {
        return Cache::get($this->credentials);
    }

    /**
     * Store the credentials in the cache.
     *
     * @param mixed $credentials
     */
    private function saveCredentials(mixed $credentials)
    {
        Cache::put($this->credentials, $credentials->access_token, 30);
    }
}

<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ClientException;
use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\ResponseInterface;
use function base64_encode;
use function config;
use function json_decode;
use function strtolower;

class Authenticator
{
    protected string $endpoint;

    public string $service = 'c2b';

    private string $credentials;

    private int $retries;

    private int $retryWaitTime;

    /**
     * Authenticator constructor.
     *
     * @param Core $engine
     * @throws ClientException
     */
    public function __construct(protected Core $engine)
    {
        $this->endpoint = EndpointsRepository::build('auth');
        $this->retries = config("drh.mpesa.retries", 0);
        $this->retryWaitTime = config("drh.mpesa.retry_wait_time", 3);
    }

    /**
     * @param string $service
     * @param MpesaAccount|null $account
     * @return string|null
     * @throws ClientException
     * @throws ExternalServiceException
     * @throws GuzzleException
     */
    public function authenticate(string $service = 'c2b', MpesaAccount $account = null): ?string
    {
        $this->service = $service;

        $this->generateCredentials($account);
//        if (config('drh.mpesa.cache_credentials', false) && !empty($key = $this->getFromCache())) {
//            return $key;
//        }
        try {
            $response = $this->makeRequest();
            $body = json_decode($response->getBody());
            mpesaLogInfo('-- AUTH RES --', [$body]);

            $this->saveCredentials($body);

            return $body->access_token;
        } catch (RequestException $exception) {
            mpesaLogError($exception->getMessage());
            throw $this->generateException($exception);
        } catch (ConnectException $exception) {
            if ($this->retries > 0) {
                mpesaLogInfo($this->retries . " auth trials left.");

                $this->retries--;
                // TODO: Implement exponential back-off
                sleep($this->retryWaitTime);
                return $this->authenticate($service, $account);
            }
            mpesaLog('CRITICAL', $exception->getMessage());
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
            'bad request - invalid credentials' =>
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
            $key = config("drh.mpesa.$this->service.consumer_key");
            $secret = config("drh.mpesa.$this->service.consumer_secret");
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
    private function saveCredentials(mixed $credentials): void
    {
        Cache::put($this->credentials, $credentials->access_token, 3590);
    }
}

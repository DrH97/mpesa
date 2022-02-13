<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use DrH\Mpesa\Repositories\MpesaRepository;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use function config;
use function strlen;

class ApiCore
{
    public bool $bulk = false;

    public string $bearer;

    private int $retries;

    private int $retryWaitTime;

    /**
     * ApiCore constructor.
     *
     * @param Core $engine
     * @param MpesaRepository $mpesaRepository
     */
    public function __construct(private Core $engine, protected MpesaRepository $mpesaRepository)
    {
        $this->retries = config("drh.mpesa.retries", 0);
        $this->retryWaitTime = config("drh.mpesa.retry_wait_time", 3);
    }

    /**
     * @param string $number
     * @param bool $strip_plus
     * @return string
     */
    public function formatPhoneNumber(string $number, bool $strip_plus = true): string
    {
        $number = preg_replace('/\s+/', '', $number);
        $replace = static function ($needle, $replacement) use (&$number) {
            if (Str::startsWith($number, $needle)) {
                $pos = strpos($number, $needle);
                $length = strlen($needle);
                $number = substr_replace($number, $replacement, $pos, $length);
            }
        };
        $replace('2547', '+2547');
        $replace('07', '+2547');
        $replace('2541', '+2541');
        $replace('01', '+2541');
        $replace('7', '+2547');
        $replace('1', '+2541');
        if ($strip_plus) {
            $replace('+254', '254');
        }
        return $number;
    }

    /**
     * @param array $body
     * @param string $endpoint
     * @param MpesaAccount|null $account
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws ExternalServiceException
     * @throws \DrH\Mpesa\Exceptions\ClientException
     */
    private function makeRequest(array $body, string $endpoint, MpesaAccount $account = null): ResponseInterface
    {
        if (config('drh.mpesa.multi_tenancy', false)) {
            $this->bearer = $this->engine->auth->authenticate($this->bulk, $account);
        } else {
            $this->bearer = $this->engine->auth->authenticate($this->bulk);
        }

        return $this->engine->client->request(
            'POST',
            $endpoint,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->bearer,
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]
        );
    }

    /**
     * @param array $body
     * @param string $endpoint
     * @param MpesaAccount|null $account
     * @return array
     * @throws GuzzleException
     * @throws ExternalServiceException|\DrH\Mpesa\Exceptions\ClientException
     */
    public function sendRequest(array $body, string $endpoint, MpesaAccount $account = null): array
    {
        $url = EndpointsRepository::build($endpoint, $account);
        try {
            $response = $this->makeRequest($body, $url, $account);
            $_body = json_decode($response->getBody(), true);

            return (array)$_body;
        } catch (RequestException $exception) {
            throw $this->generateException($exception);
        } catch (ConnectException $exception) {
            mpesaLogError($exception->getMessage());
            if ($this->retries > 0) {
                mpesaLogInfo($this->retries . " trials left.");

                $this->retries--;
                sleep($this->retryWaitTime);
                return $this->sendRequest($body, $endpoint, $account);
            }
            throw new ExternalServiceException('Mpesa Server Error');
        }
    }

    /**
     * @param RequestException $exception
     * @return ExternalServiceException
     */
    private function generateException(RequestException $exception): ExternalServiceException
    {
        mpesaLogError($exception->getMessage());
        return new ExternalServiceException($exception->getResponse()->getBody());
    }
}

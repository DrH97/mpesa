<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use DrH\Mpesa\Repositories\MpesaRepository;
use Illuminate\Support\Str;
use function config;
use function strlen;

class ApiCore
{
    /**
     * @var Core
     */
    private $engine;
    /**
     * @var bool
     */
    public $bulk = false;
    /**
     * @var Mpesa
     */
    public $mpesaRepository;

    /**
     * @var string
     */
    public $bearer;

    /**
     * ApiCore constructor.
     *
     * @param Core $engine
     * @param Mpesa $mpesa
     */
    public function __construct(Core $engine, Mpesa $mpesa)
    {
        $this->engine = $engine;
        $this->mpesaRepository = $mpesa;
    }

    /**
     * @param string $number
     * @param bool $strip_plus
     * @return string
     */
    protected function formatPhoneNumber($number, $strip_plus = true): string
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
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \DrH\Mpesa\Exceptions\MpesaException
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function makeRequest($body, $endpoint, MpesaAccount $account = null)
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
     * @return mixed
     * @throws MpesaException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendRequest($body, $endpoint, MpesaAccount $account = null)
    {
        $endpoint = EndpointsRepository::build($endpoint, $account);
        try {
            $response = $this->makeRequest($body, $endpoint, $account);
            $_body = \json_decode($response->getBody());
            if ($response->getStatusCode() !== 200) {
                throw new MpesaException($_body->errorMessage ? $_body->errorCode . ' - ' . $_body->errorMessage : $response->getBody());
            }
            return $_body;
        } catch (ClientException $exception) {
            throw $this->generateException($exception);
        }
    }

    /**
     * @param ClientException $exception
     * @return MpesaException
     */
    private function generateException(ClientException $exception): MpesaException
    {
        return new MpesaException($exception->getResponse()->getBody());
    }
}

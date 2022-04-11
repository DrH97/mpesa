<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\ExternalServiceException;
use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use DrH\Mpesa\Repositories\Mpesa;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use function config;
use function json_decode;
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
    public function formatPhoneNumber($number, $strip_plus = true): string
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
     * @return ResponseInterface
     * @throws MpesaException
     * @throws Exception
     * @throws GuzzleException
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
     * @return array
     * @throws GuzzleException
     * @throws ExternalServiceException|\DrH\Mpesa\Exceptions\ClientException
     */
    public function sendRequest($body, $endpoint, MpesaAccount $account = null)
    {
        $endpoint = EndpointsRepository::build($endpoint, $account);
        try {
            $response = $this->makeRequest($body, $endpoint, $account);
            $_body = json_decode($response->getBody());
            if ($response->getStatusCode() !== 200) {
                throw new \DrH\Mpesa\Exceptions\ClientException($_body->errorMessage ?
                    $_body->errorCode . ' - ' . $_body->errorMessage : $response->getBody());
            }
            return (array)$_body;
        } catch (ClientException $exception) {
            throw $this->generateException($exception);
        } catch (ConnectException $exception) {
            throw new ExternalServiceException('Mpesa Server Error');
        }
    }

    /**
     * @param ClientException $exception
     * @return \DrH\Mpesa\Exceptions\ClientException
     */
    private function generateException(ClientException $exception): \DrH\Mpesa\Exceptions\ClientException
    {
        mpesaLogError($exception->getMessage());
        return new \DrH\Mpesa\Exceptions\ClientException($exception->getResponse()->getBody());
    }
}

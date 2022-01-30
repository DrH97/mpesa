<?php

namespace DrH\Mpesa\Library;

use DrH\Mpesa\Exceptions\MpesaException;
use DrH\Mpesa\Repositories\EndpointsRepository;
use DrH\Mpesa\Repositories\MpesaRepository;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class ApiCore
{
    public bool $bulk = false;

    public string $bearer;

    private int $trials = 3;

    /**
     * ApiCore constructor.
     *
     * @param Core $engine
     * @param MpesaRepository $mpesaRepository
     */
    public function __construct(private Core $engine, protected MpesaRepository $mpesaRepository)
    {
    }

    /**
     * @param string $number
     * @param bool $strip_plus
     * @return string
     */
    protected function formatPhoneNumber(string $number, bool $strip_plus = true): string
    {
        $number = preg_replace('/\s+/', '', $number);
        $replace = static function ($needle, $replacement) use (&$number) {
            if (Str::startsWith($number, $needle)) {
                $pos = strpos($number, $needle);
                $length = \strlen($needle);
                $number = substr_replace($number, $replacement, $pos, $length);
            }
        };
        $replace('2547', '+2547');
        $replace('07', '+2547');
        $replace('2541', '+2541');
        $replace('01', '+2541');
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
     * @throws MpesaException
     */
    private function makeRequest(array $body, string $endpoint, MpesaAccount $account = null): ResponseInterface
    {
        if (\config('drh.mpesa.multi_tenancy', false)) {
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
     * @return mixed
     * @throws GuzzleException
     * @throws MpesaException
     */
    public function sendRequest(array $body, string $endpoint, MpesaAccount $account = null): array
    {
        $endpoint = EndpointsRepository::build($endpoint, $account);
        try {
            $response = $this->makeRequest($body, $endpoint, $account);
            $_body = json_decode($response->getBody(), true);

            return (array)$_body;
        } catch (ClientException|ServerException $exception) {
            throw $this->generateException($exception);
        } catch (ConnectException $exception) {
            mpesaLogError($exception);
            mpesaLogInfo($this->trials . " trials left.");
            if ($this->trials > 0) {
                $this->trials--;
                sleep(1);
                return $this->sendRequest($body, $endpoint, $account);
            }
            throw new MpesaException('Mpesa Server Error');
        }
    }

    /**
     * @param ClientException|ServerException $exception
     * @return MpesaException
     */
    private function generateException(ClientException|ServerException $exception): MpesaException
    {
        return new MpesaException($exception->getResponse()->getBody());
    }
}

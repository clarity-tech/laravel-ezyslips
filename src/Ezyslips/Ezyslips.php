<?php

namespace ClarityTech\Ezyslips;

use ClarityTech\Ezyslips\Api\EzyslipsResponse;
use ClarityTech\Ezyslips\Exceptions\EzyslipsApiException;
use ClarityTech\Ezyslips\Exceptions\EzyslipsCredentialsInvalidException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class Ezyslips
{
    protected static ?string $email = null;
    protected static ?string $license = null;

    public const API_URL = 'https://ezyslips.com/';
    public const PREFIX = 'api/';

    public bool $debug = false;
    
    protected array $requestHeaders = [];
    protected array $responseHeaders = [];
    protected $responseStatusCode;
    protected $reasonPhrase;

    public function __construct()
    {
        self::$email = Config::get('ezyslips.email');
        self::$license = Config::get('ezyslips.license');
    }

    public function api()
    {
        return $this;
    }

    public static function getEmail()
    {
        return self::$email;
    }

    public static function getLicense()
    {
        return self::$license;
    }

    /**
     * “exchange” your access code with the shop’s permanent API token:
     * and sets to the current instance
     */
    // protected function getBearerToken() : string
    // {
    //     $token = base64_encode(self::getEmail() . ':' . self::getLicense());

    //     return $token;
    // }


    public static function getBaseUrl() : string
    {
        return self::API_URL . self::PREFIX;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        $className = __NAMESPACE__.'\\Api\\'.ucwords($name);

        $entity = new $className();

        return $entity;
    }

    public function addHeader($key, $value) : self
    {
        $this->requestHeaders = array_merge($this->requestHeaders, [$key => $value]);

        return $this;
    }

    public function removeHeaders() : self
    {
        $this->requestHeaders = [];

        return $this;
    }

    public function setDebug(bool $status = true)
    {
        $this->debug = $status;

        return $this;
    }

    /*
     *  $args[0] is for route uri and $args[1] is either request body or query strings
     */
    public function __call($method, $args)
    {
        list($uri, $params) = [ltrim($args[0], '/'), $args[1] ?? []];
        $response = $this->makeRequest($method, $uri, $params);

        if (is_array($array = $response->json()) && count($array) == 1) {
            return array_shift($array);
        }

        return $response;
    }

    public function getHeadersForSend() : array
    {
        return $this->requestHeaders;
    }

    public function validateCredentials()
    {
        if (is_null(self::getEmail()) || is_null(self::getLicense())) {
            throw new EzyslipsCredentialsInvalidException('Please set email and license key to use the api');
        }
    }

    public function makeRequest(string $method, string $path, array $params = [])
    {
        $this->validateCredentials();

        $url = self::getBaseUrl() . $path;

        $method = strtolower($method);

        $response = Http::withOptions(['debug' => $this->debug,])
                ->withBasicAuth(self::getEmail(), self::getLicense())
                ->withHeaders($this->getHeadersForSend())
                ->$method($url, $params);

        $this->parseResponse($response->toPsrResponse());

        if ($response->successful()) {
            return $response;
        }

        return $this->throwErrors($response);
    }

    protected function parseResponse(ResponseInterface $response)
    {
        $this
            ->setResponseHeaders($response->getHeaders())
            ->setStatusCode($response->getStatusCode())
            ->setReasonPhrase($response->getReasonPhrase());
    }

    protected function throwErrors($httpResponse)
    {
        $response = $httpResponse->json();
        $psrResponse = $httpResponse->toPsrResponse();

        $statusCode = $psrResponse->getStatusCode();

        if (isset($response['errors']) || $statusCode >= 400) {
            $errorString = null;
            
            if (!is_null($response)) {
                $errorString = is_array($response['errors']) ? json_encode($response['errors']) : $response['errors'];
            }
            
            throw new EzyslipsApiException(
                $errorString ?? $psrResponse->getReasonPhrase(),
                $statusCode
            );
        }
    }

    private function setStatusCode($code)
    {
        $this->responseStatusCode = $code;
        return $this;
    }

    public function getStatusCode()
    {
        return $this->responseStatusCode;
    }

    private function setReasonPhrase($message)
    {
        $this->reasonPhrase = $message;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    private function setResponseHeaders($headers)
    {
        $this->responseHeaders = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->responseHeaders;
    }

    public function getHeader($header)
    {
        return $this->hasHeader($header) ? $this->responseHeaders[$header] : '';
    }

    public function hasHeader($header)
    {
        return array_key_exists($header, $this->responseHeaders);
    }
}

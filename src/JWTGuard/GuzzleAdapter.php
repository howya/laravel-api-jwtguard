<?php

namespace App\JWTGuard;

use RBennett\JWTGuard\Contracts\HTTPRequestContract;

class GuzzleAdapter implements HTTPRequestContract
{
    /**
     * @var Client
     */
    private $http;

    /**
     * GuzzleAdapter constructor.
     * @param $client
     */
    public function __construct($client)
    {
        $this->http = $client;
    }

    /**
     * @param string $verb
     * @param string $uri
     * @param array $headers
     * @param array $query
     * @param array $formParams
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $verb, string $uri, array $headers = [], array $query = [], array $formParams = []): array
    {
        $requestArray = [
            'query' => $query,
            'headers' => $headers,
            'form_params' => $formParams,
            'http_errors' => false
        ];


        $response = $this->http->request($verb, $uri, $requestArray);

        return [
            'headers' => $response->getHeaders(),
            'body' => json_decode($response->getBody(), true),
            'statusCode' => $response->getStatusCode()
        ];

    }


}
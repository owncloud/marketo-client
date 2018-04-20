<?php

namespace MarketoClient;

use MarketoClient\Request\RequestInterface;
use MarketoClient\Response\AccessToken;

class Client
{

    /** @var \GuzzleHttp\Client */
    public $client;

    /** @var AccessToken */
    private $accessToken;
    private $baseUri; // marketo API url
    private $clientId; // Marketo client id
    private $clientSecret; // Marketo client secret

    function __construct($baseUri, $clientId, $clientSecret)
    {
        $this->baseUri = $baseUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUri . '/rest/v1/',
        ]);
    }

    private function authenticateIfRequired()
    {
        if ($this->accessToken instanceof AccessToken && !$this->accessToken->hasExpired()) {
            return;
        }

        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUri . '/identity/'
        ]);


        $response = $client->request('GET', 'oauth/token', [
            'query' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]
        ]);

        $result = json_decode($response->getBody()->getContents());

        $this->accessToken = new AccessToken($result->access_token, $result->expires_in);

    }


    /**
     * @param RequestInterface $req
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(RequestInterface $req)
    {
        $this->authenticateIfRequired();

        $response = $this->client->request($req->getMethod(), $req->getPath(), [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->accessToken->getToken()}"
            ],
            'body' => json_encode($req),
        ]);
        $result = json_decode($response->getBody()->getContents(), true);

        return [
            'success' => $result['success'],
            'result' => $result['result'][0]
        ];
    }
}



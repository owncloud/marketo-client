<?php

namespace MarketoClient;

use MarketoClient\Client\AccessToken;
use MarketoClient\Client\AuthenticationException;

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

    /**
     * @throws AuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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



        if ($response->getStatusCode() !== 200) {
            throw new AuthenticationException($result['error_description']);
        }

        $this->accessToken = new AccessToken($result->access_token, $result->expires_in);

    }


    /**
     * @param RequestInterface $req
     * @return Response
     * @throws AuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(RequestInterface $req)
    {
        $this->authenticateIfRequired();

        try {

            $response = $this->client->request($req->getMethod(), $req->getPath(), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$this->accessToken->getToken()}"
                ],
                'body' => json_encode($req),
            ]);
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        $result = json_decode($response->getBody()->getContents(), true);

        return new Response($result);
    }
}



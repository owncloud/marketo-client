<?php

namespace MarketoClient;


use MarketoClient\Request\ProgramStatus;
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
            'headers' => ['Content-Type' => 'application/json']
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

    public function pushLead($leadData, $programName)
    {
        if (!isset($leadData['email']) || $leadData['email'] === '') {
            return false;
        }

        $this->authenticateIfRequired();

        $response = $this->client->request('POST', 'leads/push.json',
            [
                'query' => ['access_token' => $this->accessToken->getToken()],
                'body' => json_encode([
                    'action' => 'createOrUpdate',
                    'programName' => $programName,
                    'lookupField' => 'email',
                    'input' => [$leadData]
                ]),
            ]);

        $result = json_decode($response->getBody()->getContents(), true);

        return [
            'success' => $result['success'],
            'result' => $result['result'][0]
        ];
    }

    /**
     * @param ProgramStatus $status
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setProgramStatus(ProgramStatus $status)
    {
        $this->authenticateIfRequired();

        $response = $this->client->request('POST', "leads/programs/{$status->getProgramId()}/status.json", [
            'query' => ['accessToken' => $this->accessToken],
            'body' => json_encode($status),
        ]);
        $result = json_decode($response->getBody()->getContents(), true);
        return ['success' => $result['success'], 'result' => $result['result'][0]];
    }
}



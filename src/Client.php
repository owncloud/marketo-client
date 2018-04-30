<?php

namespace MarketoClient;

use MarketoClient\Client\AccessToken;
use MarketoClient\Client\AuthenticationException;
use MarketoClient\Response\Error;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /** @var \GuzzleHttp\Client */
    private $client;

    private $marketoHost; // marketo API url
    private $clientId; // Marketo client id
    private $clientSecret; // Marketo client secret

    private $persistTokenFunc;
    private $loadTokenFunc;

    private $retrying = false;

    public function __construct(string $baseUri, string $clientId, string $clientSecret)
    {
        $this->marketoHost = $baseUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;

        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @throws AuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function authenticate($forceNewToken = false): AccessToken
    {
        $token = null;

        if (!$forceNewToken && $this->isTokenPersistenceEnabled()) {
            $token = ($this->loadTokenFunc)();
        }

        if ($token instanceof AccessToken && !$token->hasExpired()) {
            return $token;
        }

        $response = $this->client->request('GET', 'oauth/token', [
            'base_uri' => "$this->marketoHost/identity/",
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

        $validUntil = time() + $result->expires_in;
        $token = new AccessToken($result->access_token, $validUntil);

        if ($this->isTokenPersistenceEnabled()) {
            ($this->persistTokenFunc)($token);
        }

        return $token;
    }


    /**
     * @param RequestInterface $req
     * @return Response
     * @throws AuthenticationException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute(RequestInterface $req) : Response
    {
        // We force a new token if the request is retried due to expired token
        $forceNewToken = $this->retrying;

        $token = $this->authenticate($forceNewToken)->getBearerToken();
        try {
            $restResponse = $this->doRequest($req, $token);
        } catch (\Exception $ex) {

        }
        $body = json_decode($restResponse->getBody()->getContents(), true);
        $marketoResponse = new Response($body);


        if (!$marketoResponse->isSuccessful() ) {
            /** @var Error[] $errors */
            $errors = $marketoResponse->getErrors();
            foreach ($errors as $error) {
                if ($error->tokenHasExpired()) {
                    // Repeat request once with new token
                    if (!$this->retrying) {
                        $this->retrying = true;
                        return $this->execute($req);
                    }

                    $this->retrying = false;
                    throw new \LogicException(
                        "Could not get a valid token after two retries: {$error->getCode()}: {$error->getMessage()}"
                    );
                }
            }
        }

        $this->retrying = false;
        return $marketoResponse;
    }

    private function doRequest(RequestInterface $req, string $bearerToken): ResponseInterface
    {
        $requestSpec = [
            'http_errors' => false,
            'base_uri' => "$this->marketoHost/rest/v1/",
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => "Bearer $bearerToken"],
            'query' => $query = $req->getQuery()
        ];

        $method = $req->getMethod();

        if ($method !== 'GET') {
            $requestSpec['body'] = json_encode($req);
        }

        try {
            $restResponse = $this->client->request($method, $req->getPath(), $requestSpec);
        } catch (\Exception $e ) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return $restResponse;
    }

    /**
     * @param callable $persistTokenFunc(AccessToken $t)
     */
    public function onTokenPersist(Callable $persistTokenFunc): void
    {
        $this->persistTokenFunc = $persistTokenFunc;
    }

    /**
     * @param callable $loadTokenFunc(): AccessToken
     */
    public function onTokenLoad(Callable $loadTokenFunc): void
    {
        $this->loadTokenFunc = $loadTokenFunc;
    }

    private function isTokenPersistenceEnabled(): bool
    {
        return $this->persistTokenFunc instanceof \Closure
            && $this->loadTokenFunc instanceof \Closure;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): \GuzzleHttp\Client
    {
        return $this->client;
    }

    /**
     * @param \GuzzleHttp\Client $client
     */
    public function setClient(\GuzzleHttp\Client $client): void
    {
        $this->client = $client;
    }
}



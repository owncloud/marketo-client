<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use MarketoClient\Client\AccessToken;

class ClientTest extends TestCase
{
    private $guzzle;
    /** @var \MarketoClient\Client */
    private $marketoClient;

    private $container;

    private $history;


    public function setUp()
    {
        $this->container = [];
        $this->history = Middleware::history($this->container);
        $this->marketoClient = new MarketoClient\Client('/', '123', '456');
    }

    public function responses(array $responses)
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push($this->history);
        $this->guzzle = new \GuzzleHttp\Client(['handler' => $stack]);


        $this->marketoClient->setClient($this->guzzle);

    }

    /**
     * @throws ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \MarketoClient\Client\AuthenticationException
     */
    public function testCredentialsArePassedOnAuth()
    {
        $this->responses([
            new Response(200, [], json_encode([
                'access_token' => 'cdf01657-110d-4155-99a7-f986b2ff13a0:int',
                'token_type' => 'bearer',
                'expires_in' => '356',
                'scope' => 'apis@acmeinc.com'
            ])),
            new Response(200, [], json_encode(['result' => [], 'success' => true]))
        ]);

        $this->marketoClient->execute($this->createMock(\MarketoClient\RequestInterface::class));

        /** @var \GuzzleHttp\Psr7\Request $authRequest */
        $authRequest = array_shift($this->container)['request'];
        $this->assertEquals(
            '/oauth/token?grant_type=client_credentials&client_id=123&client_secret=456',
            $authRequest->getRequestTarget());

        /** @var \GuzzleHttp\Psr7\Request $authenticatedRequest */
        $authenticatedRequest = array_shift($this->container)['request'];
        $authLine = $authenticatedRequest->getHeaderLine('Authorization');

        $this->assertEquals('Bearer cdf01657-110d-4155-99a7-f986b2ff13a0:int', $authLine);
    }

    public function testPersistedAuthTokensAreUsed()
    {
        $this->responses([
            new Response(200, [], json_encode(['result' => [], 'success' => true])),
            new Response(200, [], json_encode(['result' => [], 'success' => true])),
            new Response(200, [], json_encode(['result' => [], 'success' => true])),
            new Response(200, [], json_encode(['result' => [], 'success' => true])),

        ]);

        $this->marketoClient->onTokenLoad(function() {

            return new AccessToken('TOKEN', 11924930258);
        });

        $this->marketoClient->onTokenPersist(function(AccessToken $t) {
            $this->assertSame('TOKEN', $t->getBearerToken());
            $this->assertSame(11924930258, $t->getValidUntil());
        });

        $this->marketoClient->execute($this->createMock(\MarketoClient\RequestInterface::class));
        $this->marketoClient->execute($this->createMock(\MarketoClient\RequestInterface::class));
        $this->marketoClient->execute($this->createMock(\MarketoClient\RequestInterface::class));
        $this->marketoClient->execute($this->createMock(\MarketoClient\RequestInterface::class));


        foreach ($this->container as $transaction) {
            $bearer = $transaction['request']->getHeaderLine('Authorization');
            $this->assertSame('Bearer TOKEN', $bearer);

        }
    }

    public function testTokenIsRefreshedAndRequestIsRepeatedOnceAfterTokenExpiry()
    {
        $this->responses([
            new Response(200, [], json_encode([
                'access_token' => 'old-token',
                'token_type' => 'bearer',
                'expires_in' => 123,
                'scope' => 'apis@acmeinc.com'
            ])),
            new Response(401, [], json_encode([
                'result' => [],
                'success' => false,
                'errors' => [
                    [
                    'code' => '602',
                    'message' => 'token expired'
                    ]
                ]])),
            new Response(200, [], json_encode([
                'access_token' => 'new-token',
                'token_type' => 'bearer',
                'expires_in' => 11924930258,
                'scope' => 'apis@acmeinc.com'
            ])),
            new Response(200, [], json_encode([
                'result' => ['email' => 'foo@example.com', 'company' => 'ACME Corp.'],
                'success' => true
            ])),
        ]);

        $resp = $this->marketoClient->execute($this->createMock(\MarketoClient\RequestInterface::class));

        // Ignore first auth request
        array_shift($this->container);

        $bearer = array_shift($this->container)['request']->getHeaderLine('Authorization');
        $this->assertSame('Bearer old-token', $bearer);

        /** @var \GuzzleHttp\Psr7\Request $secondAuthRequest */
        $secondAuthRequest = array_shift($this->container)['request'];
        $this->assertContains('/oauth/token', $secondAuthRequest->getRequestTarget());

        /** @var \GuzzleHttp\Psr7\Request $repeatedRequestWithNewToken */
        $repeatedRequestWithNewToken = array_shift($this->container)['request'];
        $bearer = $repeatedRequestWithNewToken->getHeaderLine('Authorization');
        $this->assertSame('Bearer new-token', $bearer);

    }
}

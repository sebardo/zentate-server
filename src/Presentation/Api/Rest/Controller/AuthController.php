<?php

namespace App\Presentation\Api\Rest\Controller;

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zend\Diactoros\Response as Psr7Response;

final class AuthController
{
    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var PasswordGrant
     */
    private $passwordGrant;

    /**
     * @var ClientCredentialsGrant
     */
    private $clientCredentialsGrant;

    /**
     * AuthController constructor.
     * @param AuthorizationServer $authorizationServer
     * @param PasswordGrant $passwordGrant
     * @param ClientCredentialsGrant $clientCredentialsGrant
     */
    public function __construct(
        AuthorizationServer $authorizationServer,
        PasswordGrant $passwordGrant
//        ClientCredentialsGrant $clientCredentialsGrant
    ) {
        $this->authorizationServer = $authorizationServer;
        $this->passwordGrant = $passwordGrant;
//        $this->clientCredentialsGrant = $clientCredentialsGrant;
    }

    /**
     * Curl call to test this controller: curl -d "grant_type=password&client_id=c57c89af-7fb3-4338-98de-3028c6fda687&client_secret=test&scope=*&username=user@email.com&password=user" -X POST http://oauth.test/api/accessToken
     *
     * @Route("accessToken", name="api_get_access_token", methods={"POST"})
     * @param ServerRequestInterface $request
     * @return null|Psr7Response
     * @throws \Exception
     */
    public function getAccessToken(ServerRequestInterface $request): ?Response
    {

        $this->passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M'));

        $psrResponse =  $this->withErrorHandling(function () use ($request) {
            $this->passwordGrant->setRefreshTokenTTL(new \DateInterval('P1M'));
            $this->authorizationServer->enableGrantType(
                $this->passwordGrant,
                new \DateInterval('PT1H')
            );


            return $this->authorizationServer->respondToAccessTokenRequest($request, new Psr7Response());
        });

        $httpFoundationFactory = new HttpFoundationFactory();
        $symfonyResponse = $httpFoundationFactory->createResponse($psrResponse);

        return  $symfonyResponse;
    }

    /**
     * @Route("accessTokenClientCredencials", name="api_get_access_token_client_credentials", methods={"POST"})
     * @param ServerRequestInterface $request
     * @return null|Psr7Response
     * @throws \Exception
     */
    public function getAccessTokenClientCredentials(ServerRequestInterface $request): ?Response
    {

        $this->clientCredentialsGrant->setRefreshTokenTTL(new \DateInterval('P1M'));

        $psrResponse =  $this->withErrorHandling(function () use ($request) {
            $this->clientCredentialsGrant->setRefreshTokenTTL(new \DateInterval('P1M'));
            $this->authorizationServer->enableGrantType(
                $this->clientCredentialsGrant,
                new \DateInterval('PT1H')
            );


            return $this->authorizationServer->respondToAccessTokenRequest($request, new Psr7Response());
        });

        $httpFoundationFactory = new HttpFoundationFactory();
        $symfonyResponse = $httpFoundationFactory->createResponse($psrResponse);

        return  $symfonyResponse;
    }

    private function withErrorHandling($callback): ?Psr7Response
    {
        try {
            return $callback();
        } catch (OAuthServerException $e) {
            return $this->convertResponse(
                $e->generateHttpResponse(new Psr7Response())
            );
        } catch (\Exception $e) {
            return new Psr7Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $e) {
            return new Psr7Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function convertResponse(Psr7Response $psrResponse): Psr7Response
    {
        return new Psr7Response(
            $psrResponse->getBody(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );
    }
}
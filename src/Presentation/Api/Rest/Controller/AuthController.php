<?php

namespace App\Presentation\Api\Rest\Controller;

use App\Application\Repository\Doctrine\UserRepository;
use App\Infrastructure\oAuth2Server\Bridge\User;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
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
     * @var AuthCodeGrant
     */
    private $authCodeGrant;

    /**
     * AuthController constructor.
     * @param AuthorizationServer $authorizationServer
     * @param PasswordGrant $passwordGrant
     * @param ClientCredentialsGrant $clientCredentialsGrant
     * @param AuthCodeGrant $authCodeGrant
     * @param UserRepository $userRepository
     */
    public function __construct(
        AuthorizationServer $authorizationServer,
        PasswordGrant $passwordGrant,
        ClientCredentialsGrant $clientCredentialsGrant,
        AuthCodeGrant $authCodeGrant,
        UserRepository $userRepository
    ) {
        $this->authorizationServer = $authorizationServer;
        $this->passwordGrant = $passwordGrant;
        $this->clientCredentialsGrant = $clientCredentialsGrant;
        $this->authCodeGrant = $authCodeGrant;
        $this->userRepository = $userRepository;
    }

    /**
     * Curl call to test this controller:
     * curl -d "grant_type=password&client_id=c57c89af-7fb3-4338-98de-3028c6fda687&client_secret=test&scope=*&username=user@email.com&password=user" -X POST http://zentate-server.test/api/accessToken
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
     * Machine to machine : grant "client_credentials"
     * curl -d "grant_type=client_credentials&client_id=c57c89af-7fb3-4338-98de-3028c6fda687&client_secret=test&scope=*" -X POST http://zentate-server.test/api/accessTokenClientCredentials
     *
     * @Route("accessTokenClientCredentials", name="api_get_access_token_client_credentials", methods={"POST"})
     * @param ServerRequestInterface $request
     * @return null|Psr7Response
     * @throws \Exception
     */
    public function getAccessTokenClientCredentials(ServerRequestInterface $request): ?Response
    {
        $psrResponse =  $this->withErrorHandling(function () use ($request) {

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

    /**
     * Login in thirty party as google or face : grant "authorization code grant"
     * curl http://zentate-server.test/api/authorize?response_type=code&client_id=c57c89af-7fb3-4338-98de-3028c6fda687&redirect_uri=http://zentate.test/me&scope=*
     * http://zentate-server.test/api/authorize?response_type=code&client_id=c57c89af-7fb3-4338-98de-3028c6fda687&redirect_uri=http://zentate.test/me&scope=*
     * @Route("authorize", name="api_authorize", methods={"GET"})
     * @param ServerRequestInterface $request
     * @return null|Psr7Response
     * @throws \Exception
     */
    public function authorize(ServerRequestInterface $request): ?Response
    {
//        $this->authCodeGrant->setRefreshTokenTTL(new \DateInterval('P1M'));

        $psrResponse =  $this->withErrorHandling(function () use ($request) {
//            $this->authCodeGrant->setRefreshTokenTTL(new \DateInterval('P1M'));
            $this->authorizationServer->enableGrantType(
                $this->authCodeGrant,
                new \DateInterval('PT1H')
            );

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.
            $appUser = $this->userRepository->findOneByEmail('user@email.com');
            $oAuthUser = new User($appUser->getId()->toString());
            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser($oAuthUser); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $this->authorizationServer->completeAuthorizationRequest($authRequest, new Psr7Response());

        });

        $httpFoundationFactory = new HttpFoundationFactory();
        $symfonyResponse = $httpFoundationFactory->createResponse($psrResponse);

        return  $symfonyResponse;
    }

    /**
     *grant "auth code"
     *
     * curl -d "grant_type=authorization_code&client_id=c57c89af-7fb3-4338-98de-3028c6fda687&client_secret=test&redirect_uri=http://zentate.test/me&code=CODE" -X POST http://zentate-server.test/api/accessTokenAuthCode
     *
     * @Route("accessTokenAuthCode", name="api_get_access_token_auth_code", methods={"POST"})
     * @param ServerRequestInterface $request
     * @return null|Psr7Response
     * @throws \Exception
     */
    public function getAccessTokenAuthCode(ServerRequestInterface $request): ?Response
    {
        $psrResponse =  $this->withErrorHandling(function () use ($request) {

            $this->authorizationServer->enableGrantType(
                $this->authCodeGrant,
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

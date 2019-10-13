<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Entities\Traits\AuthCodeTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

final class AuthCode implements AuthCodeEntityInterface
{
    use AuthCodeTrait, EntityTrait, TokenEntityTrait;

    /**
     * AuthCode constructor.
     * @param string $userIdentifier
     * @param array $scopes
     */
    public function __construct(string $userIdentifier=null, array $scopes = [])
    {
        $this->setUserIdentifier($userIdentifier);
        foreach ($scopes as $scope) {
            $this->addScope($scope);
        }
    }
}

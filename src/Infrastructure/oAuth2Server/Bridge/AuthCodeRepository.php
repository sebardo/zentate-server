<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Domain\Repository\AuthCodeRepositoryInterface as AppAuthCodeRepositoryInterface;
use App\Domain\Model\AuthCode as AppAuthCode;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @var AppAuthCodeRepositoryInterface
     */
    private $appAuthCodeRepository;

    /**
     * AccessTokenRepository constructor.
     * @param AppAuthCodeRepositoryInterface $appAccessTokenRepository
     */
    public function __construct(AppAuthCodeRepositoryInterface $appAuthCodeRepository)
    {
        $this->appAuthCodeRepository = $appAuthCodeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCode();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $appAuthCode = new AppAuthCode(
            $authCodeEntity->getIdentifier(),
            $authCodeEntity->getUserIdentifier(),
            $authCodeEntity->getClient()->getIdentifier(),
            $this->scopesToArray($authCodeEntity->getScopes()),
            false,
            new \DateTime(),
            new \DateTime(),
            $authCodeEntity->getExpiryDateTime(),
            $authCodeEntity->getRedirectUri()
        );
        $this->appAuthCodeRepository->save($appAuthCode);
    }

    private function scopesToArray(array $scopes): array
    {
        return array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId): void
    {
        $appAuthCode = $this->appAuthCodeRepository->find($codeId);
        if ($appAuthCode === null) {
            return;
        }
        $appAuthCode->revoke();
        $this->appAuthCodeRepository->save($appAuthCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId): ?bool
    {
        $appAuthCode = $this->appAuthCodeRepository->find($codeId);
        if ($appAuthCode === null) {
            return true;
        }
        return $appAuthCode->isRevoked();
    }

}


<?php
namespace App\Infrastructure\oAuth2Server\Bridge;

use App\Domain\Repository\AccessTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface as AppUserRepositoryInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use App\Domain\Repository\RefreshTokenRepositoryInterface as AppRefreshTokenRepositoryInterface;
use App\Domain\Model\RefreshToken as AppRefreshToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{

    /**
     * @var AppRefreshTokenRepositoryInterface
     */
    private $appRefreshTokenRepository;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $accessTokenRepository;

    /**
     * UserRepository constructor.
     * @param AppUserRepositoryInterface $appUserRepository
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(
        AppRefreshTokenRepositoryInterface $appRefreshTokenRepository,
        AccessTokenRepositoryInterface $accessTokenRepository
    ) {
        $this->appRefreshTokenRepository = $appRefreshTokenRepository;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshToken();
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $id = $refreshTokenEntity->getIdentifier();
        $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier();
        $expiryDateTime = $refreshTokenEntity->getExpiryDateTime();

        $refreshTokenPersistEntity = new AppRefreshToken($id, $accessTokenId, $expiryDateTime);
        $this->appRefreshTokenRepository->save($refreshTokenPersistEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId): void
    {
        $refreshTokenPersistEntity = $this->appRefreshTokenRepository->find($tokenId);
        if ($refreshTokenPersistEntity === null) {
            return;
        }
        $refreshTokenPersistEntity->revoke();
        $this->appRefreshTokenRepository->save($refreshTokenPersistEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshTokenPersistEntity = $this->appRefreshTokenRepository->find($tokenId);
        if ($refreshTokenPersistEntity === null || $refreshTokenPersistEntity->isRevoked()) {
            return true;
        }
        return $this->accessTokenRepository->isAccessTokenRevoked($refreshTokenPersistEntity->getAccessTokenId());
    }
}
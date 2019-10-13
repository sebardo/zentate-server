<?php

namespace App\Application\Repository\Doctrine;

use App\Domain\Model\AuthCode;
use App\Domain\Repository\AuthCodeRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    private const ENTITY = AuthCode::class;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    /**
     * UserRepository constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->objectRepository = $this->entityManager->getRepository(self::ENTITY);
    }

    public function find(string $authCodeId): ?AuthCode
    {
        return $this->entityManager->find(self::ENTITY, $authCodeId);
    }

    public function save(AuthCode $authCode): void
    {
        $this->entityManager->persist($authCode);
        $this->entityManager->flush();
    }
}

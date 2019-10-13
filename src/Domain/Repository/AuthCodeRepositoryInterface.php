<?php

namespace App\Domain\Repository;

use App\Domain\Model\AuthCode;

interface AuthCodeRepositoryInterface
{
    public function find(string $authCodeId): ?AuthCode;

    public function save(AuthCode $authCode): void;
}

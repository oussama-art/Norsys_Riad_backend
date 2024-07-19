<?php

namespace App\Service;

use App\Entity\Token;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class TokenService
{
    private JWTTokenManagerInterface $JWTManager;
    private EntityManagerInterface $entityManager;

    public function __construct(
        JWTTokenManagerInterface $JWTManager,
        EntityManagerInterface $entityManager
    ) {
        $this->JWTManager = $JWTManager;
        $this->entityManager = $entityManager;
    }

    public function createToken(User $user): Token
    {
        $tokenValue = $this->JWTManager->create($user);

        $tokenEntity = new Token();
        $tokenEntity->setTokenName($tokenValue);
        $tokenEntity->setUser($user);
        $tokenEntity->setExpired(false);

        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return $tokenEntity;
    }

    public function invalidateToken(string $token): ?Token
    {
        $tokenEntity = $this->entityManager->getRepository(Token::class)->findOneBy(['tokenName' => $token]);

        if (!$tokenEntity) {
            return null;
        }

        $tokenEntity->setExpired(true);
        $this->entityManager->persist($tokenEntity);
        $this->entityManager->flush();

        return $tokenEntity;
    }
}

<?php

namespace App\Repository;

use App\Entity\RiadImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RiadImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RiadImage::class);
    }

    // Add custom query methods here if needed
}

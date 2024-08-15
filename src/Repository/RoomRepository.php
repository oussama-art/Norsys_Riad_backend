<?php
// src/Repository/RoomRepository.php
namespace App\Repository;

use App\Entity\Room;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;
use Doctrine\ORM\Query\Expr\Join;

class RoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Room::class);
    }

    /**
     * Finds available rooms between given dates for a specified number of guests.
     *
     * @param DateTimeImmutable $checkIn
     * @param DateTimeImmutable $checkOut
     * @param int $guests
     * @return Room[]
     */
    public function findChambresDisponibles(DateTimeImmutable $checkIn, DateTimeImmutable $checkOut, int $guests)
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.reservations', 'res', Join::WITH, 'res.start_date <= :checkOut AND res.end_date >= :checkIn')
            ->andWhere('res.id IS NULL')
            ->andWhere('r.nb_personne >= :guests')
            ->setParameter('checkIn', $checkIn)
            ->setParameter('checkOut', $checkOut)
            ->setParameter('guests', $guests);

        return $qb->getQuery()->getResult();
    }
}

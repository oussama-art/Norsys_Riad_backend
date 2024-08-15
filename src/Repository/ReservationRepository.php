<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Trouve les réservations conflictuelles pour une chambre spécifique pendant une période spécifique.
     *
     * @param string $nomChambre Le nom de la chambre à vérifier
     * @param \DateTimeInterface $dateArrivee La date d'arrivée
     * @param \DateTimeInterface $dateDepart La date de départ
     * @return Reservation[] Les réservations conflictuelles
     */
    public function findConflictingReservations(string $nomChambre, \DateTimeInterface $dateArrivee, \DateTimeInterface $dateDepart): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.room', 'room')
            ->andWhere('room.name = :nomChambre')
            ->andWhere('r.start_date <= :dateDepart')
            ->andWhere('r.end_date >= :dateArrivee')
            ->setParameter('nomChambre', $nomChambre)
            ->setParameter('dateArrivee', $dateArrivee)
            ->setParameter('dateDepart', $dateDepart)
            ->getQuery()
            ->getResult();
    }
    /**
     * Finds available rooms within a given date range that can accommodate a specified number of persons.
     *
     * @param \DateTimeInterface $dateArrivee The arrival date.
     * @param \DateTimeInterface $dateDepart The departure date.
     * @param int $nbPersonnes The number of persons.
     * @return Room[] The available rooms.
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
/**
     * @return Reservation[]
     */
    public function findReservationsByRoomAndDate(int $roomId, DateTimeImmutable $today): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.room = :roomId')
            ->andWhere('r.endDate > :today')
            ->setParameter('roomId', $roomId)
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();
    }
}

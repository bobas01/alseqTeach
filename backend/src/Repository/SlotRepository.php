<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Slot;
use App\Enum\SlotStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Slot>
 */
class SlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Slot::class);
    }

    /**
     * @return list<Slot>
     */
    public function findAvailableBetween(\DateTimeImmutable $from, \DateTimeImmutable $to, \DateTimeImmutable $now): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.startsAt >= :from')
            ->andWhere('s.startsAt < :to')
            ->andWhere('s.startsAt > :now')
            ->andWhere('s.status = :available')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('now', $now)
            ->setParameter('available', SlotStatus::Available)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Slot>
     */
    public function findBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.startsAt >= :from')
            ->andWhere('s.startsAt < :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Slot>
     */
    public function findExpiredHolds(\DateTimeImmutable $now): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.status = :held')
            ->andWhere('s.holdExpiresAt IS NOT NULL')
            ->andWhere('s.holdExpiresAt <= :now')
            ->setParameter('held', SlotStatus::Held)
            ->setParameter('now', $now)
            ->getQuery()
            ->getResult();
    }

    public function existsAt(\DateTimeImmutable $startsAt): bool
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.startsAt = :startsAt')
            ->setParameter('startsAt', $startsAt)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}

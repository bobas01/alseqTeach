<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\Slot;
use App\Entity\User;
use App\Enum\BookingStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function hasPaidForUser(User $user): bool
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.student = :user')
            ->andWhere('b.status = :paid')
            ->setParameter('user', $user)
            ->setParameter('paid', BookingStatus::Paid)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    /**
     * @return list<Booking>
     */
    public function findPendingForSlot(Slot $slot): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.slot = :slot')
            ->andWhere('b.status IN (:statuses)')
            ->setParameter('slot', $slot)
            ->setParameter('statuses', [BookingStatus::Pending, BookingStatus::AwaitingTeacher])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Booking>
     */
    public function findAwaitingTeacher(): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->addSelect('s')
            ->andWhere('b.status = :status')
            ->setParameter('status', BookingStatus::AwaitingTeacher)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return list<Booking>
     */
    public function findUpcoming(\DateTimeImmutable $from): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->addSelect('s')
            ->andWhere('b.status IN (:statuses)')
            ->andWhere('s.startsAt >= :from')
            ->setParameter('statuses', [
                BookingStatus::AwaitingTeacher,
                BookingStatus::Pending,
                BookingStatus::Paid,
            ])
            ->setParameter('from', $from)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param list<int> $slotIds
     *
     * @return array<int, Booking>
     */
    public function findActiveBySlotIds(array $slotIds): array
    {
        if ($slotIds === []) {
            return [];
        }

        /** @var list<Booking> $bookings */
        $bookings = $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->addSelect('s')
            ->andWhere('s.id IN (:ids)')
            ->andWhere('b.status IN (:statuses)')
            ->setParameter('ids', $slotIds)
            ->setParameter('statuses', [
                BookingStatus::AwaitingTeacher,
                BookingStatus::Pending,
                BookingStatus::Paid,
            ])
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($bookings as $booking) {
            $slotId = $booking->getSlot()->getId();
            if ($slotId !== null) {
                $map[$slotId] = $booking;
            }
        }

        return $map;
    }

    /**
     * @return list<Booking>
     */
    public function findPaidNeedingReminder(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('b')
            ->innerJoin('b.slot', 's')
            ->addSelect('s')
            ->andWhere('b.status = :paid')
            ->andWhere('b.reminderSentAt IS NULL')
            ->andWhere('s.startsAt >= :from')
            ->andWhere('s.startsAt < :to')
            ->setParameter('paid', BookingStatus::Paid)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->orderBy('s.startsAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

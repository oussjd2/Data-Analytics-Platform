<?php

namespace App\Repository;

use App\Entity\Leads\Leads;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

class LeadsRepository extends ServiceEntityRepository
{
    private $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        parent::__construct($registry, Leads::class);
        $this->logger = $logger;
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        try {
            $qb = $this->createQueryBuilder('l')
                ->andWhere('l.created_at >= :startDate')
                ->andWhere('l.created_at <= :endDate')
                ->setParameter('startDate', $startDate->format('Y-m-d 00:00:00'))
                ->setParameter('endDate', $endDate->format('Y-m-d 23:59:59'));

            $query = $qb->getQuery();
            $this->logger->info('Executing query: ' . $query->getSQL());
            $this->logger->info('Query parameters: ', $query->getParameters()->toArray());

            $result = $query->getResult();
            $this->logger->info('Query result count: ' . count($result));

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('Error in findByDateRange: ' . $e->getMessage(), [
                'exception' => $e,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d')
            ]);
            
            // Return an empty array instead of throwing an exception
            return [];
        }
    }
}

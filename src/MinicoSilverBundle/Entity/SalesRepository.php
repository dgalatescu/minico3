<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * SalesRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SalesRepository extends EntityRepository
{
    /**
     * @param $startDate
     * @param $endDate
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSalesByDates($startDate, $endDate, $storage = null, $product = null)
    {
        $query = $this->createQueryBuilder('sales')
            ->join('sales.productId', 'p')
            ->join('p.category', 'c')
            ->where('sales.date >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('sales.date <= :endDate')
            ->setParameter('endDate', $endDate)
            ->orderBy('sales.productId, c.name', 'ASC');

        if (!empty($storage)) {
            $query
                ->andWhere('sales.fromStorage = :storage')
                ->setParameter('storage', $storage);
        }

        if (!empty($product)) {
            $query
                ->andWhere('sales.productId = :product')
                ->setParameter('product', $product);
        }

        return $query;
    }
}

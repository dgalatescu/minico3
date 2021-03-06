<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * TransferRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TransferRepository extends EntityRepository
{
    public function getAllTransfers()
    {
        $query = $this
            ->createQueryBuilder('t')
            ->select('t, fromStorage, toStorage, product')
            ->join('t.fromStorage', 'fromStorage')
            ->join('t.toStorage', 'toStorage')
            ->join('t.product', 'product');

        return $query
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 3600)
            ->getResult();
    }
}

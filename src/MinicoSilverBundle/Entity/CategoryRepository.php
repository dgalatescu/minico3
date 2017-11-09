<?php

namespace MinicoSilverBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CategoryRepository extends EntityRepository
{
    public function getAllCategories()
    {
        $query = $this
            ->createQueryBuilder('c');

        return $query
            ->getQuery()
            ->useQueryCache(true)
            ->useResultCache(true, 3600, 'categories')
            ->getResult();
    }
}